@extends('layouts.admin')
@section('title','New Certificate Template')
@section('page_title','New Certificate Template')

@section('content')
<div class="card card-soft"><div class="card-body">
<form id="templateForm">
    @csrf
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Template Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" required maxlength="255">
        </div>
        <div class="col-md-6">
            <label class="form-label">Main Title <span class="text-danger">*</span></label>
            <input type="text" name="title_main" class="form-control" value="CERTIFICATE" required maxlength="100">
        </div>
        <div class="col-md-6">
            <label class="form-label">Subtitle <span class="text-danger">*</span></label>
            <input type="text" name="title_sub" class="form-control" value="OF INTERNSHIP" required maxlength="100">
        </div>
        <div class="col-md-6">
            <label class="form-label">Logo Position</label>
            <select name="logo_position" class="form-select">
                <option value="top-left">Top Left</option>
                <option value="top-center" selected>Top Center</option>
                <option value="top-right">Top Right</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Left Signature Title <span class="text-danger">*</span></label>
            <input type="text" name="left_signature_title" class="form-control" value="CEO" required maxlength="100">
        </div>
        <div class="col-md-6">
            <label class="form-label">Right Signature Title <span class="text-danger">*</span></label>
            <input type="text" name="right_signature_title" class="form-control" value="Program Coordinator" required maxlength="100">
        </div>
        <div class="col-md-6">
            <label class="form-label">Left Signature Name (Optional)</label>
            <input type="text" name="left_signature_name" class="form-control" placeholder="Leave blank for auto-fill" maxlength="255">
        </div>
        <div class="col-md-6">
            <label class="form-label">Right Signature Name (Optional)</label>
            <input type="text" name="right_signature_name" class="form-control" placeholder="Leave blank for auto-fill" maxlength="255">
        </div>
        <div class="col-md-4">
            <label class="form-label">Primary Color</label>
            <input type="color" name="primary_color" class="form-control form-control-color" value="#1e3a5f">
        </div>
        <div class="col-md-4">
            <label class="form-label">Secondary (Gold)</label>
            <input type="color" name="secondary_color" class="form-control form-control-color" value="#c9a84c">
        </div>
        <div class="col-md-4">
            <label class="form-label">Accent Color</label>
            <input type="color" name="accent_color" class="form-control form-control-color" value="#d4af37">
        </div>
        <div class="col-md-6">
            <label class="form-label">Font Family</label>
            <select name="font_family" class="form-select">
                <option value="Georgia, serif">Georgia</option>
                <option value="'Times New Roman', serif">Times New Roman</option>
                <option value="'Playfair Display', serif">Playfair Display</option>
                <option value="'Palatino Linotype', serif">Palatino Linotype</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Background Image</label>
            <input type="file" name="background_image" class="form-control" accept="image/*">
        </div>
        <div class="col-12 mt-4 border-top pt-3">
            <h6 class="mb-3">Visibility Settings</h6>
            <div class="form-check form-switch mb-2">
                <input type="checkbox" name="show_program_coordinator" class="form-check-input" value="1" checked id="showProgCheck">
                <label class="form-check-label" for="showProgCheck">Show Program Coordinator Signature</label>
            </div>
            <div class="form-check form-switch mb-2">
                <input type="checkbox" name="show_certificate_id" class="form-check-input" value="1" checked id="showCertIdCheck">
                <label class="form-check-label" for="showCertIdCheck">Show Certificate ID</label>
            </div>
            <div class="form-check form-switch mb-2">
                <input type="checkbox" name="show_left_signature_name" class="form-check-input" value="1" checked id="showLeftNameCheck">
                <label class="form-check-label" for="showLeftNameCheck">Show Left Signature Printed Name</label>
            </div>
            <div class="form-check form-switch mb-2">
                <input type="checkbox" name="show_right_signature_name" class="form-check-input" value="1" checked id="showRightNameCheck">
                <label class="form-check-label" for="showRightNameCheck">Show Right Signature Printed Name</label>
            </div>
            <div class="form-check form-switch mt-3">
                <input type="checkbox" name="is_active" class="form-check-input" value="1" checked id="activeCheck">
                <label class="form-check-label fw-bold" for="activeCheck">Active Template</label>
            </div>
        </div>
    </div>
    <div class="mt-4">
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check me-1"></i>Save Template</button>
        <a href="{{ route('admin.internship-certificates.templates') }}" class="btn btn-link">Cancel</a>
    </div>
</form>
</div></div>
@endsection

@push('scripts')
<script>
$('#templateForm').on('submit',function(e){
    e.preventDefault();
    const btn = $(this).find('[type="submit"]');
    btn.prop('disabled',true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
    $.ajax({
        url:'{{ route('admin.internship-certificates.templates.store') }}',
        method:'POST',
        data: new FormData(this),
        processData:false,
        contentType:false,
        success:function(res){
            Swal.fire({icon:'success',title:'Saved!',text:res.message,timer:1500,showConfirmButton:false});
            window.location.href='{{ route('admin.internship-certificates.templates') }}';
        },
        error:function(xhr){
            const msg = xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).flat().join('<br>') : (xhr.responseJSON?.message||'Error');
            Swal.fire({icon:'error',title:'Error',html:msg});
        },
        complete:function(){ btn.prop('disabled',false).html('<i class="fa-solid fa-check me-1"></i>Save Template'); }
    });
});
</script>
@endpush
