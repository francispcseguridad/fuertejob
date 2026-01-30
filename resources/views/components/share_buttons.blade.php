@php
    $shareUrl = $url ?? url()->current();
    $heading = $title ?? 'Comparte en redes';
    $shareText = $text ?? 'FuerteJob - ' . ($title ?? 'Oportunidad');
    $twitterUrl = 'https://twitter.com/intent/tweet?text=' . urlencode($shareText . ' ' . $shareUrl);
    $whatsappUrl = 'https://api.whatsapp.com/send?text=' . urlencode($shareText . ' ' . $shareUrl);
@endphp
<div class="mt-5 text-center">
    <div class="d-inline-block px-4 py-3 rounded-4 shadow-sm"
        style="background: linear-gradient(135deg, #0d6efd, #4f9cff); color: #fff;">
        <div class="mb-2 d-flex align-items-center justify-content-center gap-2">
            <i class="bi bi-stars fs-5"></i>
            <span class="fw-bold text-uppercase small letter-spacing-1">{{ $heading }}</span>
            <i class="bi bi-stars fs-5"></i>
        </div>
        <p class="mb-3 small opacity-90">Comparte en tus redes sociales o copia el enlace con el título de la
            oferta.</p>
        <div class="d-flex flex-wrap justify-content-center gap-2">
            <a class="btn btn-light btn-sm d-inline-flex align-items-center gap-2 border-0 shadow-sm"
                href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank"
                rel="noopener">
                <i class="bi bi-facebook text-primary fs-5"></i>
                <span class="fw-semibold text-primary">Facebook</span>
            </a>
            <a class="btn btn-dark btn-sm d-inline-flex align-items-center gap-2 border-0 shadow-sm"
                href="{{ $twitterUrl }}" target="_blank" rel="noopener">
                <i class="bi bi-twitter-x fs-5"></i>
                <span class="fw-semibold">X</span>
            </a>
            <a class="btn btn-light btn-sm d-inline-flex align-items-center gap-2 border-0 shadow-sm"
                href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($shareUrl) }}" target="_blank"
                rel="noopener">
                <i class="bi bi-linkedin text-primary fs-5"></i>
                <span class="fw-semibold text-primary">LinkedIn</span>
            </a>
            <a class="btn btn-success btn-sm d-inline-flex align-items-center gap-2 border-0 shadow-sm"
                href="{{ $whatsappUrl }}" target="_blank" rel="noopener">
                <i class="bi bi-whatsapp fs-5"></i>
                <span class="fw-semibold text-white">WhatsApp</span>
            </a>
            <button type="button"
                class="btn btn-outline-light btn-sm d-inline-flex align-items-center gap-2 border-0 shadow-sm share-copy"
                data-url="{{ $shareUrl }}">
                <i class="bi bi-clipboard fs-5"></i>
                <span class="fw-semibold">Copiar</span>
            </button>
        </div>
    </div>
</div>
<script>
    (function() {
        if (window.__shareButtonsBound) return;
        window.__shareButtonsBound = true;
        document.addEventListener('click', async function(e) {
            const btn = e.target.closest('.share-copy');
            if (!btn) return;
            e.preventDefault();
            const link = btn.dataset.url;
            try {
                await navigator.clipboard.writeText(link);
                btn.textContent = 'Enlace copiado';
                setTimeout(() => btn.textContent = 'Copiar enlace', 2000);
            } catch (err) {
                alert('No se pudo copiar el enlace. Copia manualmente: ' + link);
            }
        });
    })();
</script>
