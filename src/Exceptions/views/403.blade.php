@php
$defaultMsg = 'Forbidden';
@endphp

@extends('errors::layout')

@section('title', __($title ?? $defaultMsg))

@section('code', 403)

@section('message', __($message ?? $defaultMsg))