@extends('plantilla')
@section('title', 'Contacto | FuerteJob')
@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h1 class="h3 fw-bold mb-3">¿Tienes una duda o incidencia?</h1>
                        <p class="text-muted mb-4">
                            Escríbenos y te responderemos en el horario laboral lo antes posible.
                            Si estás registrado, tu usuario ya está asociado y sólo necesitas enviar el mensaje.
                        </p>

                        <form action="{{ route('contact.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            @guest
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label" for="first_name">Nombre</label>
                                        <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}"
                                            class="form-control @error('first_name') is-invalid @enderror" required>
                                        @error('first_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label" for="last_name">Apellidos</label>
                                        <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}"
                                            class="form-control @error('last_name') is-invalid @enderror" required>
                                        @error('last_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="email">Correo electrónico</label>
                                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                                        class="form-control @error('email') is-invalid @enderror" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @else
                                <input type="hidden" name="user_id" value="{{ Auth::id() }}">
                                <div class="alert alert-info rounded-3">
                                    Estás identificado como <strong>{{ Auth::user()->name }}</strong> y se usará tu correo
                                    para responderte.
                                </div>
                            @endguest

                            <div class="row g-3 mb-3">
                                @guest
                                    <div class="col-md-6">
                                        <label class="form-label" for="role_type">¿Cómo nos contactas?</label>
                                        <select name="role_type" id="role_type"
                                            class="form-select @error('role_type') is-invalid @enderror" required>
                                            <option value="">Selecciona una opción</option>
                                            @foreach ($roleTypes as $key => $label)
                                                <option value="{{ $key }}" @selected(old('role_type') === $key)>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('role_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @else
                                    <input type="hidden" name="role_type" value="{{ $authedRoleType }}">
                                    <div class="col-md-6">
                                        <label class="form-label d-block">¿Cómo nos contactas?</label>
                                        <div class="alert alert-info py-2 mb-0">
                                            Te contactas como
                                            <strong>{{ $roleTypes[$authedRoleType] ?? ucfirst($authedRoleType) }}</strong>.
                                        </div>
                                    </div>
                                @endguest
                                <div class="col-md-6">
                                    <label class="form-label" for="inquiry_type">Tipo de consulta</label>
                                    <select name="inquiry_type" id="inquiry_type"
                                        class="form-select @error('inquiry_type') is-invalid @enderror" required>
                                        <option value="">Selecciona el motivo</option>
                                        @foreach ($inquiryTypes as $key => $label)
                                            <option value="{{ $key }}" @selected(old('inquiry_type') === $key)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('inquiry_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="message">Descripción de tu consulta</label>
                                <textarea name="message" id="message" rows="5" class="form-control @error('message') is-invalid @enderror"
                                    required>{{ old('message') }}</textarea>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="attachment">Adjuntar imagen (opcional)</label>
                                <input type="file" name="attachment" id="attachment"
                                    class="form-control @error('attachment') is-invalid @enderror" accept="image/*">
                                @error('attachment')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Aceptamos imágenes JPG, PNG o WebP de hasta 4 MB.</small>
                            </div>

                            <div class="row align-items-center mb-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="math_captcha">Resuelve la operación</label>
                                    <input type="number" name="math_captcha" id="math_captcha"
                                        class="form-control @error('math_captcha') is-invalid @enderror" required>
                                    @error('math_captcha')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <p class="fs-5 fw-semibold text-center mb-0 mt-4">
                                        <span class="text-decoration-underline">{{ $captchaQuestion }}</span>
                                    </p>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 fw-bold">Enviar consulta</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @if (session('contact_success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Gracias por contactarnos',
                text: 'Te responderemos en horario laboral a la mayor brevedad posible.',
            });
        </script>
    @endif
@endsection
