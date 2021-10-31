@php
$defaultMsg = 'Page not found';
@endphp

@extends('errors::layout')

@section('title', __($title ?? $defaultMsg))

@section('code', 404)

@section('message', __($message ?? $defaultMsg))