@extends('layouts.master')

@section('title', 'Petition ' . $petitionId)

@section('footer-scripts')
    <script type="text/javascript" src="/js/moment.js/moment.min.js"></script>
    <script type="text/javascript" src="/js/chart.js-v2/Chart.min.js"></script>
    <script type="text/javascript">
        var ctx = document.getElementById("datapointChart");
        var dataChart = new Chart( ctx, {
            type: 'scatter',
            data: {
                datasets: [
                    {
                        label: 'Signatures',
                        fill: true,
                        data: {!! json_encode( $chartDataValues ) !!}
                    },
                ],
            },
            options: {
                scales: {
                    xAxes: [{
                        ticks: {
                            callback: function(value, index, values) {
                                return (new moment(value * 1000).format('D MMM YYYY HH:mm'));
                            }
                        }
                    }]
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            var date = new moment(tooltipItem.xLabel * 1000).format('D MMM YYYY HH:mm');
                            return date + ' : ' + numberWithCommas(tooltipItem.yLabel) + ' signatures';
                        }
                    }
                }

            }

        });
        var ctx2 = document.getElementById("deltaChart");
        var dataChart = new Chart( ctx2, {
            type: 'scatter',
            data: {
                datasets: [
                    {
                        label: 'Change',
                        fill: false,
                        data: {!! json_encode( $chartDeltaValues) !!},
                        pointBorderColor: '#F00'
                    }
                ]
            },
            options: {
                scales: {
                    xAxes: [{
                        ticks: {
                            callback: function(value, index, values) {
                                return (new moment(value * 1000).format('D MMM YYYY HH:mm'));
                            }
                        }
                    }]
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            var date = new moment(tooltipItem.xLabel * 1000).format('D MMM YYYY HH:mm');
                            return date + ' : ' + numberWithCommas(tooltipItem.yLabel) + ' signatures';
                        }
                    }
                }
            }
        })
        
        const numberWithCommas = function(x) {
            return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }
    </script>
@endsection

@section('body')
<div class="container centered">
    <h1>
        <a href="/">
            UK Petition Tracker
        </a>
    </h1>
    <h2>
        Petition: {{ $petitionId }}
    </h2>
    <p>
        Description:<br> <em>{{ $petition->description }}</em>
    </p>
    <p>
        This petition is: <strong>{{ $petition->status }}</strong>
    </p>
    <p>
        Link to petition: <a href="https://petition.parliament.uk/petitions/{{ $petitionId }}">https://petition.parliament.uk/petitions/{{ $petitionId }}</a>
    </p>
    <p>
        This petition has <strong>{{ number_format($petition->last_count) }}</strong> signatures (last checked on {{ $petition->last_count_timestamp }})
    </p>
</div>
<div class="container centered">
    <!-- Could use $timeFrameLabel here -->
    View: <a href="{{ route('check-petition', ['petition' => $petitionId]) }}">All Time</a> |
    <a href="{{ route('check-petition-month', ['petition' => $petitionId]) }}">30 days</a> |
    <a href="{{ route('check-petition-week', ['petition' => $petitionId]) }}">7 days</a> |
    <a href="{{ route('check-petition-day', ['petition' => $petitionId]) }}">24 hours</a>
</div>
<div class="container centered">
    <canvas id="datapointChart" width="400" height="400"></canvas>
</div>
<div class="container centered">
    <canvas id="deltaChart" width="400" height="400"></canvas>
</div>

@endsection