@extends('layouts.admin')
@section('title', 'FAQs')
@section('heading', 'FAQs')
@section('content')
    <div class="table-frame table-frame--fill">
        <div class="table-toolbar">
            <p class="table-count">{{ $faqs->total() }} {{ \Illuminate\Support\Str::plural('question', $faqs->total()) }}</p>
            <a href="{{ route('admin.faqs.create') }}" class="admin-action">Add FAQ</a>
        </div>
        <div class="table-scroll admin-scroll">
            <table class="pro-table">
                <thead>
                    <tr>
                        <th class="col-idx">#</th>
                        <th>Question</th>
                        <th>Answer</th>
                        <th class="col-status">Status</th>
                        <th class="col-action">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($faqs as $faq)
                        <tr>
                            <td class="col-idx">{{ ($faqs->firstItem() ?? 1) + $loop->index }}</td>
                            <td>
                                <span class="col-primary">{{ $faq->question }}</span>
                            </td>
                            <td>{{ filled($faq->answer) ? str($faq->answer)->limit(90) : '—' }}</td>
                            <td class="col-status"><x-admin.status-pill :value="$faq->is_active ? 'active' : 'inactive'" /></td>
                            <td class="col-action">
                                <x-admin.row-menu>
                                    <a href="{{ route('admin.faqs.edit', $faq) }}">Edit</a>
                                    <x-admin.delete-button :action="route('admin.faqs.destroy', $faq)" />
                                </x-admin.row-menu>
                            </td>
                        </tr>
                    @empty
                        <tr class="is-empty"><td colspan="5">No FAQs yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="table-pager">{{ $faqs->links() }}</div>
@endsection
