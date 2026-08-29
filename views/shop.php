<?php if (!empty($categoryBanner)): ?>
    <?php require __DIR__ . '/partials/category-banner.php'; ?>
<?php else: ?>
    <section class="page-hero compact">
        <div class="container"><p class="eyebrow">Fresh menu</p><h1>Shop Mithai & Bakery</h1></div>
    </section>
<?php endif; ?>
<section class="section">
    <div class="container">
        <div class="shop-toolbar">
            <details class="filter-disclosure">
                <summary><span>Refine &amp; Sort</span><small>Category, price and order</small></summary>
                <button class="filter-backdrop" type="button" data-filter-close aria-label="Close filters"></button>
                <div class="filter-panel">
            <div class="filter-panel-head"><div><p class="eyebrow">Shop controls</p><h2>Refine &amp; Sort</h2></div><button class="icon-button" type="button" data-filter-close aria-label="Close filters">&times;</button></div>
            <form method="get" action="<?= !empty($category) ? url('category/' . $category['slug']) : url('shop') ?>">
                <label>Search<input name="q" value="<?= h($filters['q']) ?>" placeholder="Product name"></label>
                <?php if (empty($category)): ?>
                    <label>Category<select name="category_id"><option value="0">All categories</option><?php foreach ($categories as $cat): ?><option value="<?= $cat['id'] ?>" <?= (int)$filters['category_id'] === (int)$cat['id'] ? 'selected' : '' ?>><?= h($cat['name']) ?></option><?php endforeach; ?></select></label>
                <?php else: ?>
                    <label>Category<input value="<?= h($category['name']) ?>" readonly></label>
                <?php endif; ?>
                <div class="split-fields"><label>Min<input name="min_price" type="number" value="<?= h($filters['min_price']) ?>"></label><label>Max<input name="max_price" type="number" value="<?= h($filters['max_price']) ?>"></label></div>
                <label>Sort<select name="sort"><option value="popular">Popularity</option><option value="price_asc" <?= $filters['sort']==='price_asc'?'selected':'' ?>>Price Low-High</option><option value="price_desc" <?= $filters['sort']==='price_desc'?'selected':'' ?>>Price High-Low</option><option value="newest" <?= $filters['sort']==='newest'?'selected':'' ?>>Newest</option></select></label>
                <button class="btn btn-primary" type="submit">Apply Filters</button>
            </form>
                </div>
            </details>
            <div class="results-head"><strong><?= h($total) ?> products</strong><span>Showing page <?= h($page) ?></span></div>
        </div>
        <div>
            <div class="product-grid shop-grid">
                <?php foreach ($products as $product): require __DIR__ . '/partials/product-card.php'; endforeach; ?>
            </div>
            <?php if (!$products): ?><div class="empty-state">No products found. Try another category or price range.</div><?php endif; ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= max(1, (int)ceil($total / $limit)); $i++): ?>
                    <a class="<?= $i === $page ? 'is-active' : '' ?>" href="<?= !empty($category) ? url('category/' . $category['slug'], array_merge($_GET, ['page' => $i])) : url('shop', array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</section>
