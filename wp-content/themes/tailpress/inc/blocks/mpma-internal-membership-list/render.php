<?php
/**
 * MPMA Internal Membership List block template.
 *
 * @param array $attributes Block attributes.
 */

$default_organizations = array(
	array(
		'slug'     => 'agma',
		'label'    => 'AGMA',
		'imageUrl' => '',
		'imageId'  => 0,
		'sections' => array(
			array(
				'slug'    => 'corporate',
				'label'   => 'Corporate',
				'filters' => array_merge(
					array( array( 'slug' => 'num', 'label' => '#', 'items' => array() ) ),
					array_map(
						static function ( string $letter ): array {
							return array(
								'slug'  => strtolower( $letter ),
								'label' => $letter,
								'items' => array(),
							);
						},
						range( 'A', 'Z' )
					)
				),
				'items'   => array(),
			),
			array(
				'slug'    => 'consultant',
				'label'   => 'Consultant',
				'filters' => array(),
				'items'   => array(),
			),
			array(
				'slug'    => 'academic',
				'label'   => 'Academic',
				'filters' => array(),
				'items'   => array(),
			),
			array(
				'slug'    => 'emeritus',
				'label'   => 'Emeritus',
				'filters' => array(),
				'items'   => array(),
			),
			array(
				'slug'    => 'membership-timeframes',
				'label'   => 'Membership Timeframes',
				'filters' => array_map(
					static function ( string $value ): array {
						return array(
							'slug'  => $value,
							'label' => $value,
							'items' => array(),
						);
					},
					array( '100', '75', '50', '25', '20', '15', '10', '5' )
				),
				'items'   => array(),
			),
		),
	),
	array(
		'slug'     => 'abma',
		'label'    => 'ABMA',
		'imageUrl' => '',
		'imageId'  => 0,
		'sections' => array(
			array(
				'slug'    => 'primary-manufacturer-companies',
				'label'   => 'Primary Manufacturer Companies',
				'filters' => array(),
				'items'   => array(),
			),
			array(
				'slug'    => 'associate-members',
				'label'   => 'Associate Members',
				'filters' => array(),
				'items'   => array(),
			),
		),
	),
);

$sanitize_slug = static function ( $value, string $fallback ): string {
	$slug = sanitize_title( (string) $value );
	return '' !== $slug ? $slug : $fallback;
};

$sanitize_items = static function ( $items ): array {
	if ( ! is_array( $items ) ) {
		return array();
	}

	$normalized = array();

	foreach ( $items as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}

		$label = trim( sanitize_text_field( (string) ( $item['label'] ?? '' ) ) );
		$url   = trim( esc_url_raw( (string) ( $item['url'] ?? '' ) ) );

		if ( '' === $label ) {
			continue;
		}

		$normalized[] = array(
			'label' => $label,
			'url'   => $url,
		);
	}

	return $normalized;
};

$sanitize_filters = static function ( $filters ) use ( $sanitize_slug, $sanitize_items ): array {
	if ( ! is_array( $filters ) ) {
		return array();
	}

	$normalized = array();

	foreach ( $filters as $index => $filter ) {
		if ( ! is_array( $filter ) ) {
			continue;
		}

		$label = trim( sanitize_text_field( (string) ( $filter['label'] ?? '' ) ) );
		if ( '' === $label ) {
			continue;
		}

		$items = $sanitize_items( $filter['items'] ?? array() );
		if ( empty( $items ) ) {
			continue;
		}

		$normalized[] = array(
			'slug'  => $sanitize_slug( $filter['slug'] ?? $label, 'filter-' . $index ),
			'label' => $label,
			'items' => $items,
		);
	}

	return $normalized;
};

$sanitize_sections = static function ( $sections ) use ( $sanitize_slug, $sanitize_filters, $sanitize_items ): array {
	if ( ! is_array( $sections ) ) {
		return array();
	}

	$normalized = array();

	foreach ( $sections as $index => $section ) {
		if ( ! is_array( $section ) ) {
			continue;
		}

		$label = trim( sanitize_text_field( (string) ( $section['label'] ?? '' ) ) );
		if ( '' === $label ) {
			continue;
		}

		$normalized[] = array(
			'slug'    => $sanitize_slug( $section['slug'] ?? $label, 'section-' . $index ),
			'label'   => $label,
			'filters' => $sanitize_filters( $section['filters'] ?? array() ),
			'items'   => $sanitize_items( $section['items'] ?? array() ),
		);
	}

	return $normalized;
};

$sanitize_organizations = static function ( $organizations ) use ( $sanitize_slug, $sanitize_sections ): array {
	if ( ! is_array( $organizations ) ) {
		return array();
	}

	$normalized = array();

	foreach ( $organizations as $index => $organization ) {
		if ( ! is_array( $organization ) ) {
			continue;
		}

		$label = trim( sanitize_text_field( (string) ( $organization['label'] ?? '' ) ) );
		if ( '' === $label ) {
			continue;
		}

		$image_id = isset( $organization['imageId'] ) && is_numeric( $organization['imageId'] ) ? (int) $organization['imageId'] : 0;
		$image_url = '';

		if ( $image_id > 0 ) {
			$resolved = wp_get_attachment_image_url( $image_id, 'large' );
			if ( is_string( $resolved ) && '' !== $resolved ) {
				$image_url = $resolved;
			}
		}

		if ( '' === $image_url ) {
			$image_url = trim( esc_url_raw( (string) ( $organization['imageUrl'] ?? '' ) ) );
		}

		$normalized[] = array(
			'slug'     => $sanitize_slug( $organization['slug'] ?? $label, 'organization-' . $index ),
			'label'    => $label,
			'imageUrl' => $image_url,
			'imageId'  => $image_id,
			'sections' => $sanitize_sections( $organization['sections'] ?? array() ),
		);
	}

	return $normalized;
};

$title = trim( sanitize_text_field( (string) ( $attributes['title'] ?? 'Member List' ) ) );
$subtitle = trim( sanitize_text_field( (string) ( $attributes['subtitle'] ?? 'Select an organization to view members' ) ) );
$organizations = $sanitize_organizations( $attributes['organizations'] ?? $default_organizations );

if ( empty( $organizations ) ) {
	$organizations = $sanitize_organizations( $default_organizations );
}

if ( empty( $organizations ) ) {
	return;
}

$block_id = 'mpma-membership-list-' . wp_unique_id();
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => 'mpma-membership-list',
		'id'    => $block_id,
	)
);
?>

<section <?php echo $wrapper_attributes; ?> data-membership-list>
	<div class="layout-shell">
		<div class="mpma-membership-list__inner">
			<?php if ( '' !== $title ) : ?>
				<h2 class="mpma-membership-list__title"><?php echo esc_html( $title ); ?></h2>
			<?php endif; ?>

			<?php if ( '' !== $subtitle ) : ?>
				<p class="mpma-membership-list__subtitle"><?php echo esc_html( $subtitle ); ?></p>
			<?php endif; ?>

			<div class="mpma-membership-list__org-grid" role="tablist" aria-label="<?php esc_attr_e( 'Organization selection', 'tailpress' ); ?>">
				<?php foreach ( $organizations as $organization_index => $organization ) : ?>
					<?php
					$is_active_org = 0 === $organization_index;
					$org_tab_id = $block_id . '-org-tab-' . $organization['slug'];
					$org_panel_id = $block_id . '-org-panel-' . $organization['slug'];
					$org_label = $organization['label'];
					?>
					<button
						type="button"
						id="<?php echo esc_attr( $org_tab_id ); ?>"
						class="mpma-membership-list__org-button<?php echo $is_active_org ? ' is-active' : ''; ?><?php echo '' === $organization['imageUrl'] ? ' mpma-membership-list__org-button--text-only' : ''; ?>"
						role="tab"
						aria-label="<?php echo esc_attr( $org_label ); ?>"
						aria-controls="<?php echo esc_attr( $org_panel_id ); ?>"
						aria-selected="<?php echo $is_active_org ? 'true' : 'false'; ?>"
						data-membership-org-toggle="<?php echo esc_attr( $organization['slug'] ); ?>"
					>
						<?php if ( '' !== $organization['imageUrl'] ) : ?>
							<img src="<?php echo esc_url( $organization['imageUrl'] ); ?>" alt="" class="mpma-membership-list__org-image" />
						<?php endif; ?>
						<span class="mpma-membership-list__org-label<?php echo '' !== $organization['imageUrl'] ? ' screen-reader-text' : ''; ?>"><?php echo esc_html( $org_label ); ?></span>
					</button>
				<?php endforeach; ?>
			</div>

			<div class="mpma-membership-list__org-panels">
				<?php foreach ( $organizations as $organization_index => $organization ) : ?>
					<?php
					$is_active_org = 0 === $organization_index;
					$org_panel_id = $block_id . '-org-panel-' . $organization['slug'];
					$org_tab_id = $block_id . '-org-tab-' . $organization['slug'];
					$sections = is_array( $organization['sections'] ) ? $organization['sections'] : array();
					?>
					<div
						id="<?php echo esc_attr( $org_panel_id ); ?>"
						class="mpma-membership-list__panel mpma-membership-list__org-panel<?php echo $is_active_org ? ' is-active' : ''; ?>"
						role="tabpanel"
						aria-hidden="<?php echo $is_active_org ? 'false' : 'true'; ?>"
						aria-labelledby="<?php echo esc_attr( $org_tab_id ); ?>"
						data-membership-org-panel="<?php echo esc_attr( $organization['slug'] ); ?>"
					>
						<?php if ( ! empty( $sections ) ) : ?>
							<div class="mpma-membership-list__section-nav" role="tablist" aria-label="<?php echo esc_attr( $organization['label'] . ' sections' ); ?>">
								<?php foreach ( $sections as $section_index => $section ) : ?>
									<?php
									$is_active_section = 0 === $section_index;
									$section_tab_id = $block_id . '-section-tab-' . $organization['slug'] . '-' . $section['slug'];
									$section_panel_id = $block_id . '-section-panel-' . $organization['slug'] . '-' . $section['slug'];
									?>
									<button
										type="button"
										id="<?php echo esc_attr( $section_tab_id ); ?>"
										class="mpma-membership-list__choice-button<?php echo $is_active_section ? ' is-active' : ''; ?>"
										role="tab"
										aria-controls="<?php echo esc_attr( $section_panel_id ); ?>"
										aria-selected="<?php echo $is_active_section ? 'true' : 'false'; ?>"
										data-membership-section-toggle="<?php echo esc_attr( $section['slug'] ); ?>"
									>
										<?php echo esc_html( $section['label'] ); ?>
									</button>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>

						<div class="mpma-membership-list__section-panels">
							<?php foreach ( $sections as $section_index => $section ) : ?>
								<?php
								$is_active_section = 0 === $section_index;
								$section_tab_id = $block_id . '-section-tab-' . $organization['slug'] . '-' . $section['slug'];
								$section_panel_id = $block_id . '-section-panel-' . $organization['slug'] . '-' . $section['slug'];
								$filters = is_array( $section['filters'] ) ? $section['filters'] : array();
								?>
								<div
									id="<?php echo esc_attr( $section_panel_id ); ?>"
									class="mpma-membership-list__panel mpma-membership-list__section-panel<?php echo $is_active_section ? ' is-active' : ''; ?>"
									role="tabpanel"
									aria-hidden="<?php echo $is_active_section ? 'false' : 'true'; ?>"
									aria-labelledby="<?php echo esc_attr( $section_tab_id ); ?>"
									data-membership-section-panel="<?php echo esc_attr( $section['slug'] ); ?>"
								>
									<?php if ( ! empty( $filters ) ) : ?>
										<div class="mpma-membership-list__filter-nav" role="tablist" aria-label="<?php echo esc_attr( $section['label'] . ' filters' ); ?>">
											<?php foreach ( $filters as $filter_index => $filter ) : ?>
												<?php
												$is_active_filter = 0 === $filter_index;
												$filter_tab_id = $block_id . '-filter-tab-' . $organization['slug'] . '-' . $section['slug'] . '-' . $filter['slug'];
												$filter_panel_id = $block_id . '-filter-panel-' . $organization['slug'] . '-' . $section['slug'] . '-' . $filter['slug'];
												?>
												<button
													type="button"
													id="<?php echo esc_attr( $filter_tab_id ); ?>"
													class="mpma-membership-list__filter-button<?php echo $is_active_filter ? ' is-active' : ''; ?>"
													role="tab"
													aria-controls="<?php echo esc_attr( $filter_panel_id ); ?>"
													aria-selected="<?php echo $is_active_filter ? 'true' : 'false'; ?>"
													data-membership-filter-toggle="<?php echo esc_attr( $filter['slug'] ); ?>"
												>
													<?php echo esc_html( $filter['label'] ); ?>
												</button>
											<?php endforeach; ?>
										</div>

										<div class="mpma-membership-list__filter-panels">
											<?php foreach ( $filters as $filter_index => $filter ) : ?>
												<?php
												$is_active_filter = 0 === $filter_index;
												$filter_tab_id = $block_id . '-filter-tab-' . $organization['slug'] . '-' . $section['slug'] . '-' . $filter['slug'];
												$filter_panel_id = $block_id . '-filter-panel-' . $organization['slug'] . '-' . $section['slug'] . '-' . $filter['slug'];
												?>
												<div
													id="<?php echo esc_attr( $filter_panel_id ); ?>"
													class="mpma-membership-list__panel mpma-membership-list__filter-panel<?php echo $is_active_filter ? ' is-active' : ''; ?>"
													role="tabpanel"
													aria-hidden="<?php echo $is_active_filter ? 'false' : 'true'; ?>"
													aria-labelledby="<?php echo esc_attr( $filter_tab_id ); ?>"
													data-membership-filter-panel="<?php echo esc_attr( $filter['slug'] ); ?>"
												>
													<ul class="mpma-membership-list__items" role="list">
														<?php foreach ( $filter['items'] as $item ) : ?>
															<li class="mpma-membership-list__item">
																<?php if ( '' !== $item['url'] ) : ?>
																	<a href="<?php echo esc_url( $item['url'] ); ?>" class="mpma-membership-list__item-link"><?php echo esc_html( $item['label'] ); ?></a>
																<?php else : ?>
																	<span class="mpma-membership-list__item-text"><?php echo esc_html( $item['label'] ); ?></span>
																<?php endif; ?>
															</li>
														<?php endforeach; ?>
													</ul>
												</div>
											<?php endforeach; ?>
										</div>
									<?php else : ?>
										<ul class="mpma-membership-list__items" role="list">
											<?php foreach ( $section['items'] as $item ) : ?>
												<li class="mpma-membership-list__item">
													<?php if ( '' !== $item['url'] ) : ?>
														<a href="<?php echo esc_url( $item['url'] ); ?>" class="mpma-membership-list__item-link"><?php echo esc_html( $item['label'] ); ?></a>
													<?php else : ?>
														<span class="mpma-membership-list__item-text"><?php echo esc_html( $item['label'] ); ?></span>
													<?php endif; ?>
												</li>
											<?php endforeach; ?>
										</ul>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
