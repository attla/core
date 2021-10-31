@php
$defaultMsg = 'Method not allowed';
@endphp

@extends('errors::layout')

@section('title', __($title ?? $defaultMsg))

@section('code', 405)

@section('message', __($message ?? $defaultMsg))