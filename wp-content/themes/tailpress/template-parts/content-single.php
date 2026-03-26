<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <header class="flex max-w-5xl flex-col text-left">
        <h1 class="mt-6 text-5xl font-medium tracking-tight [text-wrap:balance] text-zinc-950 sm:text-6xl"><?php the_title(); ?></h1>
    </header>

    <?php if(has_post_thumbnail()): ?>
        <div class="mt-10 sm:mt-20 max-w-4xl rounded-4xl bg-light overflow-hidden">
            <?php the_post_thumbnail('large', ['class' => 'aspect-16/10 w-full object-cover']); ?>
        </div>
    <?php endif; ?>

    <div class="entry-content max-w-3xl mt-10 sm:mt-20">
        <?php the_content(); ?>
    </div>
</article>
