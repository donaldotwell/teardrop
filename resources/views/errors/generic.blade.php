@extends('layouts.errors')

@section('title', $message ?? 'Error')
@section('code', $code ?? '500')
@section('message', $message ?? 'An Error Occurred')
@section('details', $details ?? 'Something went wrong. Please try again later.')
