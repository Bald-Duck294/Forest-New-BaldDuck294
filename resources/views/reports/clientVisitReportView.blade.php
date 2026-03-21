@include('includes.report-header')
<div class="row stickyTable">
    <div class="col-md-11">
        <table class="table">
            <thead style="min-width: 70px;">
                <tr>
                    <th colspan="3" style="text-align: center;">Organization</th>
                    <th colspan="3" style="text-align: center;">Date Range</th>
                    <th colspan="3" style="text-align: center;">Report Type</th>
                    <th colspan="3" style="text-align: center;">Generated On</th>
                </tr>
            </thead>

            <tbody style="min-width: 70px;">
                <td colspan="3" style="text-align: center;"> {{$companyName}}</td>
                <td colspan="3" style="text-align: center;"> {{$reportMonth}}</td>
                <td colspan="3" style="text-align: center;"> Client Visit Report</td>
                <td colspan="3" style="text-align: center;"> {{date('d M Y')}}</td>

            </tbody>
        </table>
    </div>
    <div class="col-md-1" style="text-align: center;margin-top: -10px;">
        <div class="row">
            <form method="post" action='{{ route("downloadClientVisitReport") }}' target="_blank">
                @csrf
           
                <input type="hidden" name="toDate" value={{$toDate}} />
                <input type="hidden" name="fromDate" value={{$fromDate}} />
                <input type="hidden" name="allData" value={{$allData}} />
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
    <div class="col-md-12" style="overflow: scroll;height:70vh">
        <table class="table">
            <tr>
                <th style="text-align: center;background-color:#B8CCE4;">Sr. No.</th>
                <th style="text-align: center;background-color:#B8CCE4;">Date</th>
                <th style="text-align: center;background-color:#B8CCE4;">Visited By</th>
                <th style="text-align: center;background-color:#B8CCE4;">Site</th>
                <th style="text-align: center;background-color:#B8CCE4;">Client Name</th>
                <th style="text-align: center;background-color:#B8CCE4;">Contact Person</th>
                <th style="text-align: center;background-color:#B8CCE4;">Contact</th>
                <th style="text-align: center;background-color:#B8CCE4;">Email</th>
                <th style="text-align: center;background-color:#B8CCE4;">Address</th>
                <th style="text-align: center;background-color:#B8CCE4;">Location</th>
                <th style="text-align: center;background-color:#B8CCE4;">Remark</th>
                <th style="text-align: center;background-color:#B8CCE4;">Next Meet Date</th>
            </tr>

            <?php $srNo = 1 ?>
            <tbody>
                @foreach($data as $item)
                <tr>
                    <td align="center">{{$srNo++}}</td>
                    <td align="center">{{date('d-m-Y h:i a', strtotime($item->datetime))}}</td>
                    <td align="center">{{$item->user_name}}</td>
                    @if($item->site_id)
                    <td align="center">{{$item->site->name}}</td>
                    @else
                    <td align="center">NA</td>
                    @endif
                    <td align="center">{{$item->client_name}}</td>
                    <td align="center">{{$item->person_met}}</td>
                    <td align="center">{{$item->person_contact}}</td>
                    <td align="center">{{$item->person_email}}</td>
                    <td align="center">{{$item->address}}</td>
                    <?php $location = json_decode($item->location); ?>
                    <td align="center"><a href="{{'https://maps.google.com/?q='.$location->lat . ',' .$location->lng}}" target="_blank">Location</a> </td>
                    <td align="center">{{$item->remark}}</td>
                    @if($item->nextmeetdatetime)
                    <td align="center">{{date('d-m-Y h:i a', strtotime($item->nextmeetdatetime))}}</td>
                    @else
                    <td align="center">NA</td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>