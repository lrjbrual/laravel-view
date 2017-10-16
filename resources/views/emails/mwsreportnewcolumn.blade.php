New column has been detected for "{{ $table }}" table with the following sample data:<br><br>
@foreach($newcols as $nc)
• {{ $nc }} : {{ $reportsampledata[$nc] }}<br><br>
@endforeach
