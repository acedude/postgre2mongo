<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>postgre2mongo</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css"
          integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css"
          integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
    <link rel="stylesheet" media="all" type="text/css" href="css/app.css"/>
    <script src="http://cdnjs.cloudflare.com/ajax/libs/vue/1.0.13/vue.min.js"></script>
    <script src="http://cdnjs.cloudflare.com/ajax/libs/vue-resource/0.1.16/vue-resource.min.js"></script>
</head>
<body>
<div class="container" id="app">
    <div class="content">
        <div class="page-header">
            <h1 class="text-center">postgre2mongo</h1>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="progress">
                    <div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0"
                         aria-valuemax="100"
                         style="width: @{{ info.completionPercent }}%;">
                        <div class="progress-bar-label">@{{ info.completionPercent }}% complete</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" id="main-summary">
            <div class="col-lg-6">
                <span v-if="info.rowsPerSecond">@{{ info.rowsPerSecond }} rows/second</span>
                <span v-if="!info.rowsPerSecond">Processing stopped</span>
            </div>
            <div class="col-lg-6 text-right">
                <span v-if="info.timeRemaining">ETA: @{{ info.timeRemaining }}</span>
                <span v-if="!info.timeRemaining">No ETA</span>
            </div>
        </div>
        <hr>

        <div class="row">
            <div class="col-lg-4" id="connections">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Connections</h3>
                    </div>
                    <div class="panel-body">
                        <table>
                            <tr>
                                <td>Postgresql:</td>
                                <td>
                                    <span class="glyphicon glyphicon-ok alert-success" v-if="connections.pgsql"></span>
                                    <span class="glyphicon glyphicon-remove alert-danger"
                                          v-if="!connections.pgsql"></span>
                                </td>
                            </tr>
                            <tr>
                                <td>Cache:</td>
                                <td>
                                    <span class="glyphicon glyphicon-ok alert-success" v-if="connections.cache"></span>
                                    <span class="glyphicon glyphicon-remove alert-danger"
                                          v-if="!connections.cache"></span>
                                </td>
                            </tr>
                            <tr>
                                <td>MongoDB:</td>
                                <td>
                                    <span class="glyphicon glyphicon-ok alert-success"
                                          v-if="connections.mongodb"></span>
                                    <span class="glyphicon glyphicon-remove alert-danger"
                                          v-if="!connections.mongodb"></span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Control</h3>
                    </div>
                    <div class="panel-body">
                        <button class="btn btn-default btn-lg btn-block" type="submit" v-on:click="toggleProcessing()">
                            @{{ info.isProcessing ? 'STOP' : 'START' }}
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-lg-4" id="time">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Time</h3>
                    </div>
                    <div class="panel-body">
                        <table>
                            <tr>
                                <td>Time started:</td>
                                <td>@{{ info.timeStarted ? info.timeStarted : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td>Time elapsed:</td>
                                <td>@{{ info.timeElapsed ? info.timeElapsed : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td>Time finished:</td>
                                <td>@{{ info.timeFinished ? info.timeFinished : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Postgresql tables</h3>
                    </div>
                    <div class="panel-body">
                        <ul class="list-unstyled">
                            <li v-for="table in tables.pgsql">@{{ table }}</li>
                            <li v-if="!tables.pgsql.length">No Postgresql tables</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-4" id="individual-progress">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Individual progress</h3>
                    </div>
                    <div class="panel-body">
                        <ul class="list-unstyled">
                            <li v-for="table in tables.pgsql">
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" aria-valuenow="60"
                                         aria-valuemin="0"
                                         aria-valuemax="100"
                                         style="width: @{{ (rows.mongodb[table] ? rows.mongodb[table] : 0)/(rows.pgsql[table] ? rows.pgsql[table] : 0)*100 }}%;"
                                         v-if="rows.pgsql[table] > 0">
                                    </div>
                                    <div class="progress-bar" role="progressbar" aria-valuenow="60"
                                         aria-valuemin="0"
                                         aria-valuemax="100"
                                         style="width: 100%;"
                                         v-if="rows.pgsql[table] == 0">
                                    </div>
                                    <div class="progress-bar-label">
                                        @{{ (rows.mongodb[table] ? number_format(rows.mongodb[table]) : 0) }}
                                        / @{{ number_format(rows.pgsql[table]) }}
                                    </div>
                                </div>
                            </li>
                            <li v-if="!tables.pgsql.length">N/A</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">MongoDB collections</h3>
                    </div>
                    <div class="panel-body">
                        <ul class="list-unstyled">
                            <li v-for="table in tables.mongodb">@{{ table }}</li>
                            <li v-if="!tables.mongodb.length">No MongoDB collections</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<script src="js/app.js"></script>
</body>
</html>
