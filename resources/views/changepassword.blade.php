@extends('layouts.app')

@section('content')

<div class="container">



    <div class="main-panel">
        <div class="content-wrapper">
        @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
                    <form method="POST" action="{{route('change-password')}}">        
                    @csrf

                    <h3 class="font-weight-bold">Ubah Password </h3>

            <hr>
            <div class="form-group mb-4">
    <div class="password-container position-relative">
        <input id="current_password" type="password" name="current_password" class="form-control" placeholder="Password Lama">
       
        <i class="toggle-password ti-eye eye-toggle"></i>
    </div>
    @if($errors->has('current_password'))
        <p class="text-danger">{{ $errors->first('current_password') }}</p>
    @endif
</div>

<div class="form-group mb-4">
    <div class="password-container position-relative">
        <input id="new_password" type="password" name="new_password" class="form-control" placeholder="Password Baru">
      
        <i class="toggle-password1 ti-eye eye-toggle"></i>
        
    </div>
    @if($errors->has('new_password'))
        <p class="text-danger">{{ $errors->first('new_password') }}</p>
    @endif
</div>

<div class="form-group mb-4">
    <div class="password-container position-relative">
        <input id="new_password_confirmation" type="password" name="new_password_confirmation" class="form-control" placeholder="Konfirmasi Password Baru">
        <i class="toggle-password2 ti-eye eye-toggle"></i>
    </div>
    @if($errors->has('new_password_confirmation'))
        <p class="text-danger">{{ $errors->first('new_password_confirmation') }}</p>
    @elseif($errors->has('new_password'))
        <p class="text-danger">{{ $errors->first('new_password_confirmation') }}</p>
    @endif
</div>


       

      
        <div class="mb-3">
            <button class="btn btn-primary" type="submit">Ubah Password</button>
        </div>
    </form>

    </div>
    </div>
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('current_password');
    const togglePasswordIcon = document.querySelector('.toggle-password');

    togglePasswordIcon.addEventListener('click', function() {
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            togglePasswordIcon.classList.remove('ti-eye');
            togglePasswordIcon.classList.add('ti-eye');
        } else {
            passwordInput.type = 'password';
            togglePasswordIcon.classList.remove('ti-eye');
            togglePasswordIcon.classList.add('ti-eye');
        }
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('new_password');
    const togglePasswordIcon = document.querySelector('.toggle-password1');

    togglePasswordIcon.addEventListener('click', function() {
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            togglePasswordIcon.classList.remove('ti-eye');
            togglePasswordIcon.classList.add('ti-eye');
        } else {
            passwordInput.type = 'password';
            togglePasswordIcon.classList.remove('ti-eye');
            togglePasswordIcon.classList.add('ti-eye');
        }
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('new_password_confirmation');
    const togglePasswordIcon = document.querySelector('.toggle-password2');

    togglePasswordIcon.addEventListener('click', function() {
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            togglePasswordIcon.classList.remove('ti-eye');
            togglePasswordIcon.classList.add('ti-eye');
        } else {
            passwordInput.type = 'password';
            togglePasswordIcon.classList.remove('ti-eye');
            togglePasswordIcon.classList.add('ti-eye');
        }
    });
});
</script>

<style>
    .password-container {
  position: relative;
}

.eye-toggle {
  position: absolute;
  top: 50%;
  right: 20px;
  transform: translateY(-50%);
  cursor: pointer;
}

</style>
@endsection