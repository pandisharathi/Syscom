@extends('layouts.admin')
@section('title','Edit Certificate Template')
@section('page_title','Edit Certificate Template')

@section('content')
<div class="card card-soft"><div class="card-body">
<form id="templateForm">
    @csrf
    @method('PUT')
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Template Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" value="{{ $certificate_template->name }}" required maxlength="255">
        </div>
        <div class="col-md-6">
            <label class="form-label">Main Title <span class="text-danger">*</span></label>
            <input type="text" name="title_main" class="form-control" value="{{ $certificate_template->title_main }}" required maxlength="100">
        </div>
        <div class="col-md-6">
            <label class="form-label">Subtitle <span class="text-danger">*</span></label>
            <input type="text" name="title_sub" class="form-control" value="{{ $certificate_template->title_sub }}" required maxlength="100">
        </div>
        <div class="col-md-6">
            <label class="form-label">Logo Position</label>
            <select name="logo_position" class="form-select">
                <option value="top-left" @selected($certificate_template->logo_position=='top-left')>Top Left</option>
                <option value="top-center" @selected($certificate_template->logo_position=='top-center')>Top Center</option>
                <option value="top-right" @selected($certificate_template->logo_position=='top-right')>Top Right</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Left Signature Title <span class="text-danger">*</span></label>
            <input type="text" name="left_signature_title" class="form-control" value="{{ $certificate_template->left_signature_title }}" required maxlength="100">
        </div>
        <div class="col-md-6">
            <label class="form-label">Right Signature Title <span class="text-danger">*</span></label>
            <input type="text" name="right_signature_title" class="form-control" value="{{ $certificate_template->right_signature_title }}" required maxlength="100">
        </div>
        <div class="col-md-6">
            <label class="form-label">Left Signature Name (Optional)</label>
            <input type="text" name="left_signature_name" class="form-control" value="{{ $certificate_template->left_signature_name }}" placeholder="Leave blank for auto-fill" maxlength="255">
        </div>
        <div class="col-md-6">
            <label class="form-label">Right Signature Name (Optional)</label>
            <input type="text" name="right_signature_name" class="form-control" value="{{ $certificate_template->right_signature_name }}" placeholder="Leave blank for auto-fill" maxlength="255">
        </div>
        <div class="col-md-4">
            <label class="form-label">Primary Color</label>
            <input type="color" name="primary_color" class="form-control form-control-color" value="{{ $certificate_template->primary_color }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Secondary (Gold)</label>
            <input type="color" name="secondary_color" class="form-control form-control-color" value="{{ $certificate_template->secondary_color }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Accent Color</label>
            <input type="color" name="accent_color" class="form-control form-control-color" value="{{ $certificate_template->accent_color }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Font Family</label>
            <select name="font_family" class="form-select">
                <option value="Georgia, serif" @selected($certificate_template->font_family=='Georgia, serif')>Georgia</option>
                <option value="'Times New Roman', serif" @selected($certificate_template->font_family=="'Times New Roman', serif")>Times New Roman</option>
                <option value="'Playfair Display', serif" @selected($certificate_template->font_family=="'Playfair Display', serif")>Playfair Display</option>
                <option value="'Palatino Linotype', serif" @selected($certificate_template->font_family=="'Palatino Linotype', serif")>Palatino Linotype</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Background Image</label>
            <input type="file" name="background_image" class="form-control" accept="image/*">
            @if($certificate_template->background_image)
                <div class="mt-2"><img src="{{ Storage::url($certificate_template->background_image) }}" height="60" class="border rounded"></div>
            @endif
        </div>
        <div class="col-12 mt-4 border-top pt-3">
            <h6 class="mb-3">Visibility Settings</h6>
            <div class="form-check form-switch mb-2">
                <input type="checkbox" name="show_program_coordinator" class="form-check-input" value="1" id="showProgCheck" @checked($certificate_template->show_program_coordinator)>
                <label class="form-check-label" for="showProgCheck">Show Program Coordinator Signature</label>
            </div>
            <div class="form-check form-switch mb-2">
                <input type="checkbox" name="show_certificate_id" class="form-check-input" value="1" id="showCertIdCheck" @checked($certificate_template->show_certificate_id)>
                <label class="form-check-label" for="showCertIdCheck">Show Certificate ID</label>
            </div>
            <div class="form-check form-switch mb-2">
                <input type="checkbox" name="show_left_signature_name" class="form-check-input" value="1" id="showLeftNameCheck" @checked($certificate_template->show_left_signature_name)>
                <label class="form-check-label" for="showLeftNameCheck">Show Left Signature Printed Name</label>
            </div>
            <div class="form-check form-switch mb-2">
                <input type="checkbox" name="show_right_signature_name" class="form-check-input" value="1" id="showRightNameCheck" @checked($certificate_template->show_right_signature_name)>
                <label class="form-check-label" for="showRightNameCheck">Show Right Signature Printed Name</label>
            </div>
            <div class="form-check form-switch mt-3">
                <input type="checkbox" name="is_active" class="form-check-input" value="1" id="activeCheck" @checked($certificate_template->is_active)>
                <label class="form-check-label fw-bold" for="activeCheck">Active Template</label>
            </div>
        </div>
    </div>
    <div class="mt-4">
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check me-1"></i>Update Template</button>
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
        url:'{{ route('admin.internship-certificates.templates.update', $certificate_template) }}',
        method:'POST',
        data: new FormData(this),
        processData:false,
        contentType:false,
        success:function(res){
            Swal.fire({icon:'success',title:'Updated!',text:res.message,timer:1500,showConfirmButton:false});
            window.location.href='{{ route('admin.internship-certificates.templates') }}';
        },
        error:function(xhr){
            const msg = xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).flat().join('<br>') : (xhr.responseJSON?.message||'Error');
            Swal.fire({icon:'error',title:'Error',html:msg});
        },
        complete:function(){ btn.prop('disabled',false).html('<i class="fa-solid fa-check me-1"></i>Update Template'); }
    });
});
</script>
@endpush
