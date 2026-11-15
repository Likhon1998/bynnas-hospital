@extends('layouts.admin')
@section('title', 'Acknowledgements')
@section('heading', 'Acknowledgements')
@section('content')
    <div class="table-frame table-frame--fill">
        <div class="table-toolbar">
            <p class="table-count">{{ $acknowledgements->total() }} {{ \Illuminate\Support\Str::plural('acknowledgement', $acknowledgements->total()) }}</p>
            <a href="{{ route('admin.acknowledgements.create') }}" class="admin-action">Add acknowledgement</a>
        </div>
        <div class="table-scroll admin-scroll">
            <table class="pro-table">
                <thead>
                    <tr>
                        <th class="col-idx">#</th>
                        <th>Bangla message</th>
                        <th class="col-count">Order</th>
                        <th class="col-status">Status</th>
                        <th class="col-action">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($acknowledgements as $acknowledgement)
                        <tr>
                            <td class="col-idx">{{ ($acknowledgements->firstItem() ?? 1) + $loop->index }}</td>
                            <td>
                                <span class="col-primary" lang="bn">{{ $acknowledgement->message }}</span>
                            </td>
                            <td class="col-count">{{ $acknowledgement->sort_order }}</td>
                            <td class="col-status"><x-admin.status-pill :value="$acknowledgement->is_active ? 'active' : 'inactive'" /></td>
                            <td class="col-action">
                                <x-admin.row-menu>
                                    <a href="{{ route('admin.acknowledgements.edit', $acknowledgement) }}">Edit</a>
                                    <x-admin.delete-button :action="route('admin.acknowledgements.destroy', $acknowledgement)" />
                                </x-admin.row-menu>
                            </td>
                        </tr>
                    @empty
                        <tr class="is-empty"><td colspan="5">No acknowledgements yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="table-pager">{{ $acknowledgements->links() }}</div>
@endsection
