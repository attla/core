@php
$defaultMsg = 'Unauthorized';
@endphp

@extends('errors::layout')

@section('title', __($title ?? $defaultMsg))

@section('code', 401)

@section('message', __($message ?? $defaultMsg))