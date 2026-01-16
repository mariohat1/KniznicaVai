<?php
/** @var array $books */
/** @var IAuthenticator $auth */
/** @var LinkGenerator $link */
/** @var array $copies */
/** @var array $categories */
/** @var array $genres */
/** @var array $bookMeta */
/** @var array $filters */
/** @var TYPE_NAME $view */

use Framework\Core\IAuthenticator;
use Framework\Support\LinkGenerator;

$view->setLayout('root');
?>
<div class="container">
    <h1 class="mb-4 section-title">Knihy</h1>

    <form id="bookSearchForm" method="get" action="<?= $link->url('book.index') ?>" class="mb-4">
        <input type="hidden" name="c" value="book">

        <!-- Filter radio dots -->
        <div class="mb-3 d-flex align-items-center gap-3">
            <span class="text-muted small">Filtrovať:</span>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="filter" id="filterTitle" value="title"
                       <?= (isset($filters['filter']) ? ($filters['filter'] === 'title' ? 'checked' : '') : 'checked') ?>>
                <label class="form-check-label" for="filterTitle">
                    <i class="bi bi-book me-1"></i>Názov
                </label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="filter" id="filterAuthor" value="author"
                       <?= isset($filters['filter']) && $filters['filter'] === 'author' ? 'checked' : '' ?>>
                <label class="form-check-label" for="filterAuthor">
                    <i class="bi bi-person me-1"></i>Autor
                </label>
            </div>
        </div>

        <!-- Search and filters -->
        <div class="row g-2">
            <div class="col-12 col-md-4">
                <input id="bookSearchInput" name="q" type="search" class="form-control"
                       placeholder="Vyhľadať..." value="<?= htmlspecialchars($filters['q'] ?? '') ?>">
            </div>

            <div class="col-6 col-md-3">
                <select id="bookCategorySelect" name="category" class="form-select">
                    <option value="">Kategória</option>
                    <?php if (!empty($categories)): foreach ($categories as $id => $cat): ?>
                        <option value="<?= htmlspecialchars((string)$id) ?>" <?= isset($filters['category']) && (string)$filters['category'] === (string)$id ? 'selected' : '' ?>><?= htmlspecialchars((string)$cat) ?></option>
                    <?php endforeach; endif; ?>
                </select>
            </div>

            <div class="col-6 col-md-3">
                <select id="bookGenreSelect" name="genre" class="form-select">
                    <option value="">Žáner</option>
                    <?php if (!empty($genres)) : foreach ($genres as $id => $gen): ?>
                        <option value="<?= htmlspecialchars((string)$id) ?>" <?= isset($filters['genre']) && (string)$filters['genre'] === (string)$id ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string)$gen) ?>
                        </option>
                    <?php endforeach;
                    endif; ?>
                </select>
            </div>

            <div class="col-12 col-md-2">
                <button id="bookSearchBtn" class="btn btn-primary w-100" type="submit">
                    <i class="bi bi-search"></i><span class="d-none d-md-inline ms-1">Hľadať</span>
                </button>
            </div>
        </div>
    </form>

    <!-- Server-side rendered list -->
    <div id="booksListContainer">
        <?php if (empty($books)): ?>
            <div class="alert alert-info">Žiadne knihy nenájdené.</div>
        <?php else: ?>
            <div class="list-group list-group-flush">
                <?php foreach ($books as $b):
                     $bid = (int)$b->getId();
                    // copies mapping provides ['available' => int, 'reserved' => int]
                    $meta = $copies[$bid] ?? ['available' => 0, 'reserved' => 0];
                    $m = $bookMeta[$bid] ?? ['author' => '', 'category' => '', 'genre' => ''];
                    $authorName = htmlspecialchars((string)($m['author'] ?? ''));
                    $categoryName = htmlspecialchars((string)($m['category'] ?? ''));
                    $genreName = htmlspecialchars((string)($m['genre'] ?? ''));
                    $title = htmlspecialchars((string)$b->getTitle());
                    $isbn = htmlspecialchars((string)$b->getIsbn());
                    $year = htmlspecialchars((string)$b->getYearPublished());
                    $desc = htmlspecialchars((string)$b->getDescription());
                    $photo = htmlspecialchars((string)$b->getPhoto());
                    $bookUrl = $link->url('book.view', ['id' => $b->getId()]);
                    ?>
                    <div class="list-group-item border-bottom py-3 card card-book" data-title="<?= $title ?>"
                         data-author="<?= $authorName ?>" data-isbn="<?= $isbn ?>" data-year="<?= $year ?>"
                         data-category="<?= $categoryName ?>" data-genre="<?= $genreName ?>">
                        <div class="row align-items-start g-3 g-md-4">
                            <div class="col-12 col-md-2 text-center">
                                 <?php if (!empty($photo)): ?>
                                     <div class="bg-light d-flex align-items-center justify-content-center border rounded thumb">
                                         <img src="<?= $photo ?>" alt="<?= $title ?>" class="thumb-img">
                                     </div>
                                 <?php else: ?>
                                     <div class="bg-light d-flex align-items-center justify-content-center border rounded thumb">
                                          <svg xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice"
                                               fill="currentColor" class="text-secondary bi bi-book" viewBox="0 0 16 16">
                                              <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811V2.828zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492V2.687zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783z"/>
                                          </svg>
                                     </div>
                                 <?php endif; ?>
                            </div>

                            <div class="col-12 col-md-10">
                                 <h4 class="mb-1">
                                     <a href="<?= $bookUrl ?>"
                                        class="text-decoration-none book-title"><?= $title ?: 'Bez názvu' ?></a>
                                 </h4>

                                 <p class="mb-1 text-muted">
                                     <small>
                                         <span class="meta-label"><strong>Autor:</strong> <a href="<?= $link->url('author.view', ['id' => $m['author_id'] ?? 0]) ?>" class="author-link"><?= $authorName ?></a></span>
                                         <span class="mx-1">|</span>
                                         <span class="meta-label"><strong>ISBN:</strong> <?= $isbn ?></span>
                                         <span class="mx-1">|</span>
                                         <span class="meta-label"><strong>Rok:</strong> <?= $year ?></span>
                                     </small>
                                 </p>

                                 <!-- description removed per request: index should not show full book description -->

                                 <div>
                                    <span class="badge bg-success-subtle text-success border border-success">
                                        <i class="bi bi-check-circle me-1"></i>Dostupné
                                        <span class="ms-2"><?= htmlspecialchars((string)($meta['available'] ?? 0)) ?> / <?= htmlspecialchars((string)($meta['total'] ?? 0)) ?></span>
                                    </span>
                                 </div>
                            </div>

                        </div>

                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (!empty($pagination) && isset($pagination['pages']) && $pagination['pages'] > 1):
                $total = (int)$pagination['total'];
                $pages = (int)$pagination['pages'];
                $current = max(1, (int)($pagination['page'] ?? 1));
                $perPage = (int)($pagination['perPage'] ?? 10);
                $baseFilters = $filters ?? [];
                ?>
                <nav aria-label="Knihy - stránkovanie" class="mt-3">
                    <ul class="pagination">
                        <li class="page-item <?= $current <= 1 ? 'disabled' : '' ?>">
                            <?php $p = max(1, $current - 1);
                            $url = $link->url('book.index', array_merge($baseFilters, ['page' => $p])); ?>
                            <a class="page-link" href="<?= htmlspecialchars($url) ?>" data-page="<?= $p ?>"
                               aria-label="Predchádzajúca"><span aria-hidden="true">&laquo;</span></a>
                        </li>

                        <?php for ($i = 1; $i <= $pages; $i++):
                            $url = $link->url('book.index', array_merge($baseFilters, ['page' => $i]));
                            ?>
                            <li class="page-item <?= $i === $current ? 'active' : '' ?>">
                                <a class="page-link" href="<?= htmlspecialchars($url) ?>"
                                   data-page="<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?= $current >= $pages ? 'disabled' : '' ?>">
                            <?php $p = min($pages, $current + 1);
                            $url = $link->url('book.index', array_merge($baseFilters, ['page' => $p])); ?>
                            <a class="page-link" href="<?= htmlspecialchars($url) ?>" data-page="<?= $p ?>"
                               aria-label="Nasledujúca"><span aria-hidden="true">&raquo;</span></a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</div>

