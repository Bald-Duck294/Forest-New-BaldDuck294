@php
    $hideGlobalFilters = true;
    $hideBackground = true;
@endphp
@extends('layouts.app');
<style>
    table.dataTable th:nth-child(1) {
        width: 50px;
    }

    table.dataTable td:nth-child(1) {
        width: 50px;
    }

    table.dataTable th:nth-child(3) {
        width: 130px;
    }

    table.dataTable td:nth-child(3) {
        width: 130px;
    }

    table.dataTable th:nth-child(4) {
        width: 130px;
    }

    table.dataTable td:nth-child(4) {
        width: 130px;
    }

    table.dataTable th:nth-child(5) {
        width: 130px;

        word-break: break-all;
    }

    table.dataTable td:nth-child(5) {
        width: 130px;
        word-break: break-all;
    }

    .swal-button--cancel {
        background-color: #efefef;
        color: #555;
    }

    .swal-button--danger {
        background-color: red;
    }

    .button:hover {
        background: 'red';
    }
</style>

<div class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-8">
                        <h4>
                            @if (isset($siteName))
                                {{ $siteName->name }} -
                            @endif
                            Shifts
                        </h4>
                    </div>
                    <div class="col-md-4 text-right">
                        <?php $user = session('user'); ?>

                        <!-- <a href="{{ route('sites.getsites', $client_id) }}"><button type="button" class="btn btn-warning btn-border btn-round">Back</button></a> -->
                        @if ($user->role_id != '4')
                            <a href="{{ route('clients.getshiftscreate', [$client_id, $site_id]) }}"><button
                                    type="button" class="simple-button"><i class="la la-plus" title="add"></i>Add
                                    shift</button></a>
                        @endif
                    </div><!-- /.col -->
                </div><!-- /.row -->

            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped example">
                    <thead>
                        <tr>
                            <th>Sr. No.</th>
                            <th>Shift Name</th>
                            <th>Shift Start Time</th>
                            <th>Shift End Time</th>
                            @if ($user->role_id != '4')
                                <th>Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        <?php $sr = 1; ?>
                        @foreach ($shifts as $row)
                            <tr>
                                <td>{{ $sr++ }}</td>
                                <td>{{ $row->shift_name }}</td>
                                <?php $a = json_decode($row->shift_time); ?>
                                <td><?php echo date('h:i a', strtotime($a->start)); ?></td>

                                <td><?php echo date('h:i a', strtotime($a->end)); ?></td>
                                @if ($user->role_id != '4')
                                    <td>

                                        <a style="display:inline;"
                                            href='{{ route('clients.shift_edit', [$row->id, $client_id, $site_id]) }}'><button
                                                class="action-edit"><i class="la la-edit"
                                                    title="edit"></i></button></a>


                                        <button
                                            onclick="deleteShift('{{ $row['id'] }}','{{ $client_id }}','{{ $site_id }}')"
                                            class="action-delete"><i class="la la-trash" title="delete"></i></button>

                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>


<script type="text/javascript">
    async function deleteShift(id, client_id, site_id) {
        var flag = "delete";
        var deleted = await showSweetAlert('Delete Confirmation', 'Are you sure you want to delete this shift?',
            'Delete', true, 'Cancel');

        if (deleted == true) {
            var url = '{{ route('clients.shift_delete', [':id', ':client_id', ':site_id']) }}';
            url = url.replace(':id', id);
            url = url.replace(':client_id', client_id);
            url = url.replace(':site_id', site_id);
            window.location = url;
        }
    }

    function goBack() {
        window.history.back();
    }
</script>
