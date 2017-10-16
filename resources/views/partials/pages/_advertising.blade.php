<h3>{{ ucfirst(trans('home.fbarefunds2')) }}</h3>
<br>
<table class="table" style="width: 100%">
<thead>
    <tr class="headerSubscription">
        <th width="15%" class="card-header text-center align_middle"></th>
        <th width="50%" class="card-header background-orange text-center text-white">Managed Service</th>
        <!-- <th width="20%" class="card-header background-orange text-center text-white">DIY (Do It Yourself)</th> -->
    </tr>
</thead>
<tbody>
    <tr class="bodySubscription" style="background:#D9EDF7">
        <td class="text-center">Fees</td>
        <td class="text-center">{{ trans('home.refundsrecover') }}</td>
        <!-- <td class="text-center">{{ trans('home.refundsrecoverdiy1') }} <span class="fbaRateDiy">$30</span> {{ trans('home.refundsrecoverdiy2') }}</td> -->
    </tr>
    <tr class="bodySubscription">
      <td></td>
      <td><a class="btn pricing-button signup-color" href="/register">Sign Up</a></td>
      <!-- <td><a class="btn pricing-button signup-color" href="/register">Sign Up</a></td> -->
    </tr>
</tbody>
</table>