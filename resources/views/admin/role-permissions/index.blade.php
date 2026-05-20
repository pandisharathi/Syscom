@extends('layouts.admin')
@section('title','Roles & permissions')
@section('page_title','Roles & permissions')

@section('content')
<p class="text-muted small">Super Admin role is immutable. Changes apply immediately for users with that role.</p>
@foreach($roles as $role)
    @continue($role->slug === \App\Models\User::ROLE_SUPER_ADMIN)
    <div class="card card-soft mb-3">
        <div class="card-body">
            <div class="fw-semibold mb-3">{{ $role->name }}</div>
            <form class="role-form" data-role="{{ $role->id }}">
                <div class="row">
                    @foreach($permissions as $group => $perms)
                        <div class="col-lg-4 mb-3">
                            <div class="text-uppercase small text-secondary mb-2">{{ $group ?: 'General' }}</div>
                            @foreach($perms as $p)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="{{ $p->id }}" id="p{{ $role->id }}_{{ $p->id }}"
                                        @checked($role->permissions->contains('id',$p->id))>
                                    <label class="form-check-label" for="p{{ $role->id }}_{{ $p->id }}">{{ $p->name }}</label>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
                <button type="submit" class="btn btn-sm btn-primary rounded-pill">Save {{ $role->name }}</button>
            </form>
        </div>
    </div>
@endforeach
@endsection

@push('scripts')
<script>
$('.role-form').on('submit', function(e){
    e.preventDefault();
    const roleId=$(this).data('role');
    const ids = $(this).find('input[type=checkbox]:checked').map((i,x)=>parseInt(x.value,10)).get();
    fetch(`/admin/role-permissions/${roleId}`, {method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':$('meta[name=csrf-token]').attr('content')}, body:JSON.stringify({permission_ids:ids})})
        .then(()=>Swal.fire({icon:'success',title:'Saved',timer:900,showConfirmButton:false}));
});
</script>
@endpush
