@php
$defaultMsg = 'Service unavailable';
@endphp

@extends('errors::layout')

@section('title', __($title ?? $defaultMsg))

@section('code', 503)

@section('message', __($message ?? $defaultMsg))