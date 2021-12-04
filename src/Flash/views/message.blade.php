@if ($errors->has('csrf'))
<div @class([
    'alert alert-warning alert-dismissible',
    (!empty($last) ? $last : '')            => !empty($last) ? $last : '',
    'rounded-0'                             => !empty($flat),
]) role="alert">
    @foreach($errors->get('csrf') as $csrf)
    {!! $csrf !!}
    @endforeach
    <button type="button" class="btn-close" data-bs-dismiss="alert" data-bs-toggle="tooltip" title="Esconder" onclick="$('.tooltip').remove()"></button>
</div>
@elseif ($errors->any() && !empty($showErrors))
<div @class([
    'alert alert-danger alert-dismissible',
    (!empty($last) ? $last : '')            => !empty($last) ? $last : '',
    'rounded-0'                             => !empty($flat),
]) role="alert">
    @foreach($errors->all() as $error)
    {{ $error }}<br/>
    @endforeach
    <button type="button" class="btn-close" data-bs-dismiss="alert" data-bs-toggle="tooltip" title="Esconder" onclick="$('.tooltip').remove()"></button>
</div>
@endif

@foreach (session('__flash', collect()) as $flash)
    <div @class([
        'alert',
        'alert-' . $flash->type,
        'alert-dismissible'                     => $flash->dismissible,
        (!empty($between) ? $between : 'mb-2')  => !$loop->last,
        (!empty($last) ? $last : '')            => $loop->last,
        'rounded-0'                             => !empty($flat),
    ]) role="alert">
        {!! $flash->message !!}
        @if($flash->dismissible)
        <button type="button" class="btn-close" data-bs-dismiss="alert" data-bs-toggle="tooltip" title="Esconder" onclick="$('.tooltip').remove()"></button>
        @endif
    </div>
@endforeach

<script>
window.onload = function(){
    $('div.alert').not('.alert-dismissible').delay({{ !empty($timeout) ? (int) $timeout : 3500}}).fadeOut(350);
}
</script>
{{ session()->forget('__flash') }}