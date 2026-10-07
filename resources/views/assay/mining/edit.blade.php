@extends('layouts.app')
@section('title', 'Edit Mining Record')
@section('page-title', 'Mining and Assay Records')
@section('content')
<div style="max-width:680px;">
    <div class="page-header">
        <h1 class="page-title">Edit Mining Record</h1>
        <a href="{{ route('assay.index', ['tab' => 'mining']) }}" class="btn-cancel">&larr; Back</a>
    </div>
    @include('assay.mining._form', ['action' => route('assay.mining.update', $miningRecord), 'method' => 'PUT'])
</div>
@endsection
