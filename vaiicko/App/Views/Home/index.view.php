<?php

/** @var \Framework\Support\LinkGenerator $link */
/** @var bool $mustLogin */
$login = $mustLogin;

?>
<?php if ($login): ?>
    <script src="<?= $link->asset('js/loginClick.js') ?>"></script>
<?php endif; ?>
<section class="container-fluid" role="region" aria-labelledby="home-hero">
    <div class="row">
        <div class="col mt-5">
            <div class="text-center">
                <h1 id="home-hero" class="section-title">Knižnica DNN</h1>
                <p class="lead mb-2">Rezervujte si knihy online a vyzdvihnite ich pohodlne v knižnici.</p>

                <p class="visually-hidden">Táto stránka popisuje postup rezervácie kníh: vyhľadanie, rezervovanie a
                    vyzdvihnutie.</p>

                <div class="row justify-content-center mb-4">
                    <div class="col-10 col-sm-6 col-md-3 col-lg-2">
                        <a class="btn btn-success d-inline-flex align-items-center justify-content-center w-100 w-md-auto" href="<?= $link->url('book.index') ?>" role="button" aria-label="Prehliadať knihy">
                            <i class="bi bi-search me-2" aria-hidden="true"></i>
                            <span>Prehliadať knihy</span>
                        </a>
                    </div>
                </div>
                <div class="row row-cols-1 row-cols-md-3 g-3 mb-4 justify-content-center">
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-body text-center text-md-start">
                                <h5 class="card-title">1. Vyhľadajte</h5>
                                <p class="card-text small text-muted mb-0">Prezerajte knihy podľa kategórií alebo žánrov, alebo použite vyhľadávanie.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-body text-center text-md-start">
                                <h5 class="card-title">2. Rezervujte</h5>
                                <p class="card-text small text-muted mb-0">V detaile knihy vyberte dostupnú kópiu a kliknite na <strong>Rezervovať</strong>.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-body text-center text-md-start">
                                <h5 class="card-title">3. Vyzdvihnite</h5>
                                <p class="card-text small text-muted mb-0">Príďte do knižnice počas otváracích hodín; personál pripraví a vydá vašu rezerváciu.</p>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="mt-3 small text-muted">
                    <strong>Otváracie hodiny:</strong> Po–Pia 9:00–18:00, So 10:00–13:00.<br>
                    <strong>Kontakt:</strong> <a href="mailto:info@kniznica.example">info@kniznica.example</a> · Tel:
                    +421 2 123 4567
                </div>

            </div>
        </div>
    </div>
</section>
