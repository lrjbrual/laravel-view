<div class="container">
Date and Time: {{ $date }} 
</br>
Cron Name: {{ $cron_name }}
<br>
Time Start: {{ $data['time_start'] }}
<br>
Time Ended: {{ $data['time_end'] }}
<br>
Total Run Time: {{ $data['total_time_of_execution'] }}
<?php if($data['isError']){ ?>
<br>
Is Error: True
<?php } else { ?>
<br>
Is Error: False
<br>
Total Records Fetch: {{ $data['total_records'] }}
<?php } ?>
<br>
Message: {{ $data['message'] }}
<br>
Number of Tries: {{ $data['tries'] }}
<br>
</div>
