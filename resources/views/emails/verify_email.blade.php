@extends('themes.email_template')
@section('title', 'Email Verification')
@section('content')
    <center>
    <h1>Here is your verification code</h1>
    <h1>{{ $verification_code }}</h1>
    </center>
@endsection
@section('additionalJS')
@endsection
