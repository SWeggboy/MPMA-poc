/**
 * Ensure MPMA category appears above Genesis Blocks in the inserter.
 */
(function () {
    if (!window.wp || !wp.domReady || !wp.data) {
        return;
    }

    var MPMA_SLUG = 'mpma-custom';
    var GENESIS_SLUG = 'genesis-blocks';

    var reorderCategories = function () {
        var selector = wp.data.select('core/blocks');
        var dispatcher = wp.data.dispatch('core/blocks');

        if (
            !selector ||
            !dispatcher ||
            typeof selector.getCategories !== 'function' ||
            typeof dispatcher.setCategories !== 'function'
        ) {
            return;
        }

        var categories = selector.getCategories();
        if (!Array.isArray(categories) || categories.length === 0) {
            return;
        }

        var mpmaCategory = null;
        var ordered = [];
        var genesisIndex = -1;

        categories.forEach(function (category) {
            if (!category || !category.slug) {
                return;
            }

            if (category.slug === MPMA_SLUG) {
                mpmaCategory = category;
                return;
            }

            ordered.push(category);

            if (category.slug === GENESIS_SLUG && genesisIndex === -1) {
                genesisIndex = ordered.length - 1;
            }
        });

        if (!mpmaCategory) {
            return;
        }

        if (genesisIndex === -1) {
            ordered.unshift(mpmaCategory);
        } else {
            ordered.splice(genesisIndex, 0, mpmaCategory);
        }

        dispatcher.setCategories(ordered);
    };

    wp.domReady(function () {
        reorderCategories();
        setTimeout(reorderCategories, 0);
        setTimeout(reorderCategories, 300);
    });
})();

