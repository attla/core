@php
$defaultMsg = 'Too many requests';
@endphp

@extends('errors::layout')

@section('title', __($title ?? $defaultMsg))

@section('code', 429)

@section('message', __($message ?? $defaultMsg))