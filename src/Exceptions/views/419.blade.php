@php
$defaultMsg = 'Page expired';
@endphp

@extends('errors::layout')

@section('title', __($title ?? $defaultMsg))

@section('code', 419)

@section('message', __($message ?? $defaultMsg))