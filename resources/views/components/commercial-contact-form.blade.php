@props([
    'originLabel',
    'originValue',
    'formTitle' => 'Publicita tu marca en FuerteJob',
    'formDescription' =>
        'Cuéntanos qué necesitas y diseñamos una propuesta para tu academia, inmobiliaria o espacio comercial.',
    'buttonText' => 'Solicitar información',
    'captchaQuestion' => null,
])

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="d-flex flex-column gap-3">


            @if (session('commercial_contact_success'))
                <div class="alert alert-success mb-0 shadow-sm" role="alert">
                    Gracias por escribirnos. Te responderemos lo antes posible.
                </div>
            @endif
        </div>

        <form action="{{ route('commercial_contacts.store') }}" method="POST" class="row g-3 mt-3">
            @csrf
            <input type="hidden" name="origin" value="{{ $originValue }}">

            <div class="col-12">
                <label class="form-label fw-semibold" for="contact_name">Nombre y apellidos</label>
                <input type="text" name="name" id="contact_name" class="form-control" value="{{ old('name') }}"
                    placeholder="Ej: Laura Martín" required>
                @error('name')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold" for="contact_phone">Teléfono</label>
                <input type="text" name="phone" id="contact_phone" class="form-control" value="{{ old('phone') }}"
                    placeholder="Ej: 600 123 456" required>
                @error('phone')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold" for="contact_email">Correo electrónico</label>
                <input type="email" name="email" id="contact_email" class="form-control" value="{{ old('email') }}"
                    placeholder="ejemplo@tuempresa.com" required>
                @error('email')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="col-md-12">
                <label class="form-label fw-semibold" for="contact_detail">Detalle de la solicitud</label>
                <textarea name="detail" id="contact_detail" class="form-control" rows="3"
                    placeholder="Cuéntanos qué tipo de campaña o anuncio necesitas">{{ old('detail') }}</textarea>
                @error('detail')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold" for="math_captcha">Captcha personalizado</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-dark fw-semibold">¿Cuánto es
                        {{ $captchaQuestion ?? '2 + 3' }}?</span>
                    <input type="text" name="math_captcha" id="math_captcha" class="form-control"
                        placeholder="Resultado" value="{{ old('math_captcha') }}" required>
                </div>
                @error('math_captcha')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary px-4">Solicitar información</button>
            </div>
        </form>
    </div>
</div>
