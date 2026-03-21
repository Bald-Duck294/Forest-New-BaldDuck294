@include('includes.report-header')
<div class="row stickyTable">
    <div class="col-md-11">
        <table class="table">
            <thead style="min-width: 70px;">
                <tr>
                    <th colspan="3" style="text-align: center;">Organization</th>
                    <th colspan="3" style="text-align: center;">Client</th>
                    <th colspan="3" style="text-align: center;">Site</th>
                    <th colspan="3" style="text-align: center;">Date Range</th>
                    <th colspan="3" style="text-align: center;">Report Type</th>
                    <th colspan="3" style="text-align: center;">Generated On</th>

                </tr>
            </thead>
            <tbody style="min-width: 70px;">

                <tr>
                    @php
                    $site = App\SiteDetails::where('id', $siteId)->first();
                    $companyName = App\CompanyDetails::where('id', $site->company_id)->first();
                    @endphp
                    <td colspan="3" style="text-align: center;">{{$companyName->name}}</td>
                    <td colspan="3" style="text-align: center;"> {{ $site->client_name }} </td>
                    <td colspan="3" style="text-align: center;"> {{ $site->name }}</td>
                    <td colspan="3" style="text-align: center;">{{date('d M Y',strtotime($date))}}</td>
                    <td colspan="3" style="text-align: center;"> Visitor Summary Report</td>
                    <td colspan="3" style="text-align: center;"><?php echo date("d M Y"); ?></td>
                </tr>

            </tbody>
        </table>
    </div>
    <div class="col-md-1" style="text-align: center;margin-top: -10px;">
        <div class="row">
            <form method="get" action='{{ route("downloadVisitorSummaryReport") }}'  target="_blank">
                <input type="hidden" name="siteId" value={{$siteId}} />
                <input type="hidden" name="date" value={{$date}} />
                <div class="col-md-12" style="display: flex;justify-content: center;">
                    <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true" style="border: 1px solid grey;padding: 3px 8px;border-radius: 50%;">×</button>
                </div>
                <div class="col-md-12" style="padding: 3px;display: flex;justify-content: center;">
                    <button type="submit" class="btn btn-danger btn-border btn-round" name="xlsx" value="pdf"><i class="la la-download" title="pdf"></i>PDF</button>
                </div>
                <div class="col-md-12" style="padding: 3px;display: flex;justify-content: center;">
                    <button type="submit" class="btn btn-success btn-border btn-round" name="xlsx" value="xlsx"><i class="la la-download" title="excel"></i>Excel</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12" style="overflow: scroll;height:70vh;">
        <table class="table">
            <thead>
                <tr>
                    <th style="border: 1px solid black;background-color:#B8CCE4;">Sr. No.</th>
                    <th style="border: 1px solid black;text-align:center;background-color:#B8CCE4;">Visitor Name</th>
                    <th style="border: 1px solid black;text-align:center;background-color:#B8CCE4;">Contact No.</th>
                    <th style="border: 1px solid black;text-align:center;background-color:#B8CCE4;">Whom to Meet</th>
                    <th style="border: 1px solid black;text-align:center;background-color:#B8CCE4;">Purpose of Visit</th>
                    <th style="border: 1px solid black;text-align:center;background-color:#B8CCE4;">Address</th>
                    <th style="border: 1px solid black;text-align:center;background-color:#B8CCE4;">Remarks</th>
                    <th style="border: 1px solid black;text-align:center;background-color:#B8CCE4;">Date</th>
                    <th style="border: 1px solid black;text-align:center;background-color:#B8CCE4;">Entry Time</th>
                    <th style="border: 1px solid black;text-align:center;background-color:#B8CCE4;">Exit Time</th>
                    <th style="border: 1px solid black;text-align:center;background-color:#B8CCE4;">Total Time Spent</th>

                </tr>
            </thead>
            <?php $srNo = 1 ?>
            <tbody>
                @foreach($VisitorDetails as $item)
                @php
                $guardName = App\User::where('id', $item->guard_id)->get();
                @endphp
                <tr>
                    <td style="text-align: center;">{{$srNo}}</td>

                    <td style="text-align: center;">{{$item->name}}</td>
                    <td style="text-align: center;">{{$item->mobile}}</td>
                    <td style="text-align: center;">{{$item->personToMeet}}</td>
                    <td style="text-align: center;">{{$item->purpose}}</td>
                    <td style="text-align: center;">{{$item->address}}</td>
                    <td style="text-align: center;">{{$item->remark}}</td>
                    <td style="text-align: center;">{{$item->date}}</td>
                    <td style="text-align: center;">{{$item->entry_time}}</td>
                    <td style="text-align: center;">{{$item->exit_time}}</td>
                    <td style="text-align: center;">{{$item->duration}}</td>

                </tr>
                <?php $srNo = $srNo + 1; ?>
                @endforeach
            </tbody>
        </table>

    </div>
</div>
@include('includes.report-footer')