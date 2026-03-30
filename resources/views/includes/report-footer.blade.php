</body>
<script src="{{ URL('assets/js/core/jquery.3.2.1.min.js') }}"></script>
<script src="{{ URL('assets/js/plugin/jquery-ui-1.12.1.custom/jquery-ui.min.js') }}"></script>
<script src="{{ URL('assets/js/core/popper.min.js') }}"></script>
<script src="{{ URL('assets/js/core/bootstrap.min.js') }}"></script>
<script src="{{ URL('assets/js/ready.min.js') }}"></script>


<script>
    function goBack() {
        window.history.back();
    }

    $(document).ready(function() {
        $('#excel').on('click', function() {
            $.ajax({
                type: 'get',
                url: '{{ URL::to('downloadDailyTour') }}',
                responseType: 'blob',
                data: {
                    'type': $('#type').val(),
                    'geofences': $('#geofences').val(),
                    'tourDate': $('#tourDate').val(),
                    'userId': $('#userId').val(),
                }
            }).then((response) => {
                var url = "{{ URL::to('downloadDailyTour') }}"

                window.location = url;
            });
        });
    });
</script>

</html>
