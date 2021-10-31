@php
$defaultMsg = 'Server error';
@endphp

@extends('errors::layout')

@section('title', __($title ?? $defaultMsg))

@section('code', 500)

@section('message', __($message ?? $defaultMsg))