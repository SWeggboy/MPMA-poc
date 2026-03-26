#!/usr/bin/env python3

import argparse
import csv
import sys
import urllib.error
import urllib.request
import xml.etree.ElementTree as ET
from collections import Counter, defaultdict
from pathlib import Path


NS = {
    "wp": "http://wordpress.org/export/1.2/",
    "content": "http://purl.org/rss/1.0/modules/content/",
    "excerpt": "http://wordpress.org/export/1.2/excerpt/",
}

TARGET_TERM_MAP = {
    "courses": "courses",
    "in-person-courses": "in-person-courses",
    "live-online-courses": "live-online-courses",
    "on-demand-courses": "on-demand-courses",
    "online-workforce-training": "online-workforce-training",
    "upcoming-courses": "upcoming-courses",
    "webinars": "webinars",
    "on-demand-webinars": "on-demand-webinars",
    "emerging-technology-on-demand-webinars": "on-demand-emerging-technology-webinars",
    "emerging-technology-upcoming-webinars": "upcoming-emerging-technology-webinars",
    "trade-on-demand-webinars": "on-demand-trade-webinars",
    "trade-webinars": "on-demand-trade-webinars",
    "upcoming-webinars": "upcoming-webinars",
    "trade-upcoming-webinars": "upcoming-trade-webinars",
    "committee-meeting": "committee-meetings",
}

EXCLUDED_SOURCE_TERMS = {"events", "working-group"}


def parse_args():
    parser = argparse.ArgumentParser(
        description="Dry-run AGMA WXR event export into local TEC taxonomy mapping."
    )
    parser.add_argument("xml_path", help="Path to the AGMA WordPress export XML file.")
    parser.add_argument(
        "--include-status",
        action="append",
        dest="statuses",
        help="Statuses to include. Defaults to publish only. Repeatable.",
    )
    parser.add_argument(
        "--verify-live",
        action="store_true",
        help="Check whether reconstructed https://www.agma.org/event/{slug}/ URLs return HTTP 200.",
    )
    parser.add_argument(
        "--output-csv",
        help="Optional path to write the dry-run rows as CSV.",
    )
    return parser.parse_args()


def get_text(item, path, namespace=NS):
    return item.findtext(path, default="", namespaces=namespace).strip()


def build_live_url(post_name, source_link):
    if post_name:
        return f"https://www.agma.org/event/{post_name}/"
    return source_link.replace("https://agmastag.wpenginepowered.com", "https://www.agma.org")


def check_live_url(url):
    request = urllib.request.Request(url, headers={"User-Agent": "Mozilla/5.0"})
    try:
        with urllib.request.urlopen(request, timeout=20) as response:
            return response.getcode()
    except urllib.error.HTTPError as exc:
        return exc.code
    except Exception:
        return None


def load_rows(xml_path, included_statuses, verify_live=False):
    root = ET.parse(xml_path).getroot()
    items = root.find("channel").findall("item")

    rows = []
    unmapped_terms = Counter()
    source_term_counts = Counter()

    for item in items:
        if get_text(item, "wp:post_type") != "event":
            continue

        status = get_text(item, "wp:status")
        if status not in included_statuses:
            continue

        source_terms = []
        mapped_terms = []
        for category in item.findall("category"):
            if category.attrib.get("domain") != "event-category":
                continue

            nicename = category.attrib.get("nicename", "").strip()
            if not nicename:
                continue

            source_terms.append(nicename)
            source_term_counts[nicename] += 1

            if nicename in EXCLUDED_SOURCE_TERMS:
                continue

            target_term = TARGET_TERM_MAP.get(nicename)
            if target_term:
                mapped_terms.append(target_term)
            else:
                unmapped_terms[nicename] += 1

        mapped_terms = list(dict.fromkeys(mapped_terms))
        if not mapped_terms:
            continue

        post_name = get_text(item, "wp:post_name")
        source_link = get_text(item, "link")
        live_url = build_live_url(post_name, source_link)
        live_status = check_live_url(live_url) if verify_live else ""

        rows.append(
            {
                "source_id": get_text(item, "wp:post_id"),
                "title": get_text(item, "title"),
                "status": status,
                "post_name": post_name,
                "source_terms": ",".join(source_terms),
                "mapped_terms": ",".join(mapped_terms),
                "source_link": source_link,
                "live_url": live_url,
                "live_status": live_status,
                "excerpt_length": len(get_text(item, "excerpt:encoded")),
                "content_length": len(get_text(item, "content:encoded")),
            }
        )

    return rows, source_term_counts, unmapped_terms


def write_csv(path, rows):
    fieldnames = [
        "source_id",
        "title",
        "status",
        "post_name",
        "source_terms",
        "mapped_terms",
        "source_link",
        "live_url",
        "live_status",
        "excerpt_length",
        "content_length",
    ]
    with open(path, "w", newline="", encoding="utf-8") as handle:
        writer = csv.DictWriter(handle, fieldnames=fieldnames)
        writer.writeheader()
        writer.writerows(rows)


def print_report(rows, source_term_counts, unmapped_terms, included_statuses, verify_live):
    mapped_term_counts = Counter()
    for row in rows:
        for term in row["mapped_terms"].split(","):
            if term:
                mapped_term_counts[term] += 1

    print("Dry Run Summary")
    print(f"Included statuses: {', '.join(sorted(included_statuses))}")
    print(f"Candidate rows: {len(rows)}")
    print(f"Unique titles: {len({row['title'] for row in rows})}")
    print()
    print("Mapped Target Term Counts")
    for term in sorted(mapped_term_counts):
        print(f"- {term}: {mapped_term_counts[term]}")

    print()
    print("Included Source Term Counts")
    for term, count in source_term_counts.most_common():
        print(f"- {term}: {count}")

    print()
    print("Unmapped Source Terms")
    if unmapped_terms:
        for term, count in unmapped_terms.most_common():
            print(f"- {term}: {count}")
    else:
        print("- none")

    if verify_live:
        live_counts = Counter(str(row["live_status"]) for row in rows)
        print()
        print("Live URL Status Counts")
        for status, count in sorted(live_counts.items()):
            print(f"- {status}: {count}")

    print()
    print("Sample Rows")
    for row in rows[:15]:
        print(
            f"- {row['title']} | mapped={row['mapped_terms']} | source={row['source_terms']} | live={row['live_url']} | live_status={row['live_status']}"
        )


def main():
    args = parse_args()
    statuses = set(args.statuses or ["publish"])
    xml_path = Path(args.xml_path).expanduser()

    if not xml_path.exists():
        print(f"XML file not found: {xml_path}", file=sys.stderr)
        return 1

    rows, source_term_counts, unmapped_terms = load_rows(
        xml_path, statuses, verify_live=args.verify_live
    )

    print_report(rows, source_term_counts, unmapped_terms, statuses, args.verify_live)

    if args.output_csv:
        output_path = Path(args.output_csv).expanduser()
        output_path.parent.mkdir(parents=True, exist_ok=True)
        write_csv(output_path, rows)
        print()
        print(f"Wrote CSV: {output_path}")

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
