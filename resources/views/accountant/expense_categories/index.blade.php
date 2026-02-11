@extends('layouts.vali')

@section('content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Expense Categories</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="#">Dashboard</a></li>
                    <li class="active">Expense Categories</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <strong>Add New Category</strong>
                </div>
                <div class="card-body card-block">
                    <form method="POST" action="{{ route('accountant.expense_categories.store') }}" class="form-horizontal">
                        @csrf
                        <div class="row form-group">
                            <div class="col col-md-3"><label for="name" class="form-control-label">Name</label></div>
                            <div class="col-12 col-md-9">
                                <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control" required maxlength="150" placeholder="e.g., Utilities" />
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col col-md-3"><label for="description" class="form-control-label">Description</label></div>
                            <div class="col-12 col-md-9">
                                <textarea id="description" name="description" class="form-control" rows="2" maxlength="500" placeholder="Optional hint about this category.">{{ old('description') }}</textarea>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Add Category</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Categories</strong>
                    <a href="{{ route('accountant.expenses.create') }}" class="btn btn-outline-primary btn-sm"><i class="fa fa-file-text-o"></i> Record Expense</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th style="width: 150px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $category)
                                    <tr>
                                        <td>{{ $category->name }}</td>
                                        <td>{{ $category->description }}</td>
                                        <td>
                                            <span class="badge badge-{{ $category->status == 'Active' ? 'success' : 'secondary' }}">{{ $category->status }}</span>
                                        </td>
                                        <td>
                                            <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal{{ $category->expense_categoryID }}">
                                                <i class="fa fa-edit"></i> Edit
                                            </button>
                                            <form action="{{ route('accountant.expense_categories.destroy', $category->expense_categoryID) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this category? If it is in use, it will be set to Inactive instead.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i> Delete</button>
                                            </form>

                                            <!-- Edit Modal -->
                                            <div class="modal fade" id="editModal{{ $category->expense_categoryID }}" tabindex="-1" role="dialog" aria-labelledby="editModalLabel{{ $category->expense_categoryID }}" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="editModalLabel{{ $category->expense_categoryID }}">Edit Category</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <form method="POST" action="{{ route('accountant.expense_categories.update', $category->expense_categoryID) }}">
                                                            @csrf
                                                            <div class="modal-body">
                                                                <div class="form-group">
                                                                    <label for="name{{ $category->expense_categoryID }}">Name</label>
                                                                    <input type="text" class="form-control" id="name{{ $category->expense_categoryID }}" name="name" value="{{ $category->name }}" required maxlength="150">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="description{{ $category->expense_categoryID }}">Description</label>
                                                                    <textarea class="form-control" id="description{{ $category->expense_categoryID }}" name="description" rows="2" maxlength="500">{{ $category->description }}</textarea>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="status{{ $category->expense_categoryID }}">Status</label>
                                                                    <select class="form-control" id="status{{ $category->expense_categoryID }}" name="status">
                                                                        <option value="Active" {{ $category->status == 'Active' ? 'selected' : '' }}>Active</option>
                                                                        <option value="Inactive" {{ $category->status == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                                <button type="submit" class="btn btn-primary">Save changes</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No categories defined yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
