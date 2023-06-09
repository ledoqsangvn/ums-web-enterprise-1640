@extends('layouts.main')
@if (Auth::user()->roleID == 1)
    @section('title', 'Dashboard')
@elseif (Auth::user()->roleID != 1)
    @section('title', 'Home')
@endif
@section('content')
    @if (Auth::user()->roleID == 1)
        <h3 class="text-center fw-bold">Dashboard</h3>
    @else
        @if ((Auth::check() && Auth::user()->roleID == 4) || Auth::user()->roleID == 5)
            @if ($passDate != 1)
                <div class="d-flex justify-content-center mb-3">
                    <a href="/ideas/add" class="btn btn-success"><i class="fa-solid fa-plus me-2"></i>Add</a>
                </div>
            @else
                <div class="d-flex justify-content-center mb-3">
                    <div class="alert alert-danger">
                        <div><i class="fa-solid fa-triangle-exclamation me-2"></i>Can't <b>add idea</b>.</div>
                    </div>
                </div>
            @endif
        @endif
        @if ($countDoc > 0)
            @if (Auth::check() && Auth::user()->roleID == 2)
                <div class="d-flex justify-content-center mb-3">
                    <a href="/document/download" class="btn btn-success"><i class="fa-solid fa-download me-2"></i>Download
                        all
                        documents</a>
                </div>
            @endif
        @else
        @endif
        <div class="row">
            <div class="col-12 col-lg-9">
                @if (Auth::user()->roleID != 1)
                    @if ($ideas->isNotEmpty())
                        @foreach ($ideas as $idea)
                            <div class="card mb-3">
                                {{-- <img src="..." class="card-img-top" alt="..."> --}}
                                <div class="card-body">
                                    <h3 class="card-title fw-bold"><a
                                            href="/ideas/view/{{ $idea->ideaID }}">{{ $idea->ideaName }}</a></h3>
                                    <div class="card-text">
                                        <div>Category: <b>{{ $idea->category->categoryName }}</b></div>
                                        <div>Upload by: <b>Anonymous</b> at
                                            <?php $date = date_create($idea->created_at);
                                            echo date_format($date, 'h:i A m/d/Y');
                                            ?>
                                        </div>
                                        <div><i class="fa-solid fa-eye me-2"></i>View: {{ $idea->view }}</div>
                                        </p>
                                        @if ($idea->uploader == Auth::user()->userID)
                                            <div class="dropdown">
                                                <a type="button" class="dropdown-toggle" data-bs-toggle="dropdown"
                                                    aria-expanded="false">More
                                                </a>
                                                <ul class="dropdown-menu">
                                                    <li><a href="/ideas/edit/{{ $idea->ideaID }}" class="dropdown-item"><i
                                                                class="fa-solid fa-pen-to-square me-2"></i>Edit</a></li>
                                                    <li>
                                                        <form method="POST" action="/ideas/delete/{{ $idea->ideaID }}">
                                                            @csrf
                                                            <input name="_method" type="hidden" value="GET">
                                                            <a type="button" class="show_delete dropdown-item"
                                                                data-toggle="tooltip"><i
                                                                    class="fa-solid fa-trash me-2"></i>Delete</a>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center h4">There is no idea yet !</div>
                    @endif
                    <div class="d-flex justify-content-center mt-3">
                        {{ $ideas->links() }}
                    </div>
                @else
                @endif
            </div>
            <div class="col-12 col-lg-3 mt-3 mt-lg-0">
                <div class="card w-100 mb-3">
                    <div class="card-body">
                        <h5 class="card-title fw-bold text-decoration-underline">User:</h5>
                        <p class="card-text">
                            @foreach ($users->where('roleID', Auth::user()->roleID) as $user)
                                @if ($user->fullname == Auth::user()->fullname)
                                    <div><a
                                            href="/account/view-profile/{{ Auth::user()->userID }}"><b>{{ $user->fullname }}</a>
                                        (You)</b>
                                    </div>
                                @else
                                    <div><a href="/account/view-profile/{{ $user->userID }}">{{ $user->fullname }}</a>
                                    </div>
                                @endif
                            @endforeach
                        </p>
                    </div>
                </div>
                <div class="card w-100 mb-3">
                    <div class="card-body">
                        <h5 class="card-title fw-bold text-decoration-underline">Most popular idea:</h5>
                        <p class="card-text">
                            @if ($countIdea > 0)
                                <div class="fw-bold h5"><a
                                        href="/ideas/view/{{ $mostLikeIdea->ideaID }}">{{ $mostLikeIdea->ideaName }}</a>
                                </div>
                                <div><i class="fa-solid fa-clock"></i>
                                    <?php $date = date_create($mostLikeIdea->created_at);
                                    echo date_format($date, 'h:i A d/m/Y'); ?>
                                </div>
                            @else
                                <div>There is no idea yet !</div>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="card w-100 mb-3">
                    <div class="card-body">
                        <h5 class="card-title fw-bold text-decoration-underline">Most viewed idea:</h5>
                        <p class="card-text">
                            @if ($countIdea > 0)
                                <div class="fw-bold h5"><a
                                        href="/ideas/view/{{ $mostViewIdea->ideaID }}">{{ $mostViewIdea->ideaName }}</a>
                                </div>
                                <div><i class="fa-solid fa-clock"></i>
                                    <?php $date = date_create($mostViewIdea->created_at);
                                    echo date_format($date, 'h:i A d/m/Y'); ?>
                                </div>
                            @else
                                <div>There is no idea yet !</div>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="card w-100 mb-3">
                    <div class="card-body">
                        <h5 class="card-title fw-bold text-decoration-underline">Latest idea:</h5>
                        <p class="card-text">
                            @if ($countIdea > 0)
                                <div class="fw-bold h5"><a
                                        href="/ideas/view/{{ $latestIdea->ideaID }}">{{ $latestIdea->ideaName }}</a>
                                </div>
                                <div><i class="fa-solid fa-clock"></i>
                                    <?php $date = date_create($latestIdea->created_at);
                                    echo date_format($date, 'h:i A d/m/Y'); ?>
                                </div>
                            @else
                                <div>There is no idea yet !</div>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="card w-100">
                    <div class="card-body">
                        <h5 class="card-title fw-bold text-decoration-underline">Latest comment:</h5>
                        <p class="card-text">
                            @if ($countComment == 0)
                                <div>There is no comment yet !</div>
                            @else
                                <div class="h5 fw-bold">
                                    <a href="/ideas/view/{{ $latestComment->ideaID }}">{{ $latestCommentIdeaName }}</a>
                                </div>
                                <div class="h6 mb-0 fw-bold">Anonymous:</div>
                                <div>{{ $latestComment->commentContent }}</div>
                                <div><i class="fa-solid fa-clock"></i>
                                    <?php $date = date_create($latestComment->created_at);
                                    echo date_format($date, 'h:i A d/m/Y'); ?>
                                </div>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif
    @if (Auth::user()->roleID == 1)
        <div class="row row-cols-1 mb-3">
            <div class="col">
                <div class="card w-100">
                    <div class="card-body">
                        <h5 class="card-title fw-bold text-center">Closure date</h5>
                        <p class="card-text">
                        <div class="h1 mb-0 fw-bold text-center text-danger">
                            <?php $date = date_create($end);
                            echo date_format($date, 'd/m/Y'); ?>
                            @if ($passDate == 1)
                                <span class="h4">(Expired)</span>
                            @elseif($passDate == 0)
                            @endif
                        </div>
                        </p>
                        <a href="/ideas/acayear" class="stretched-link"></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="row row-cols-1 row-cols-lg-2 g-3">
            <div class="col">
                <div class="card w-100">
                    <div class="card-body">
                        <h5 class="card-title fw-bold text-center">Total idea(s) of Academic department:</h5>
                        <p class="card-text">
                        <div class="h1 mb-0 fw-bold text-center">{{ $countAcaIdea }}</div>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card w-100">
                    <div class="card-body">
                        <h5 class="card-title fw-bold text-center">Total idea(s) of Support department:</h5>
                        <p class="card-text">
                        <div class="h1 mb-0 fw-bold text-center">{{ $countSupIdea }}</div>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row row-cols-1 row-cols-lg-2 g-3 mt-auto">
            <div class="col">
                <div class="text-center card p-3">
                    <h4 class="fw-bold mb-3">Idea(s) per department</h4>
                    <canvas id="pieChart" class="mb-3 w-25 h-25 mx-auto"></canvas>
                    <div>Total ideas: <b>{{ $countAllIdea }}</b> idea(s)</div>
                    <div>Academic ideas: <b>{{ $countAcaIdea }}</b> idea(s)</div>
                    <div>Support ideas: <b>{{ $countSupIdea }}</b> idea(s)</div>
                </div>
            </div>
            <div class="col">
                <div class="text-center card p-3">
                    <h4 class="fw-bold mb-0">Idea(s) per month per department</h4>
                    <canvas id="lineChart" class="mb-3 w-100 h-100 mx-auto"></canvas>
                </div>
            </div>
        </div>
    @endif
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @if (Auth::user()->roleID == 1)
        <script type="text/javascript">
            var ctx = document.getElementById("pieChart").getContext('2d');
            var myChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Academic idea', 'Support idea'],
                    datasets: [{
                        data: [{{ $countAcaIdea }}, {{ $countSupIdea }}],
                        borderWidth: 1
                    }]
                },
                options: {
                    plugins: {
                        resposive: true,
                        legend: {
                            display: false,
                        }
                    }
                }
            });
        </script>
        <script type="text/javascript">
            var ctx = document.getElementById("lineChart").getContext('2d');
            var lineChart = new Chart(ctx, {
                type: 'line',
                data: {
                    datasets: [{
                        label: 'Academic department',
                        type: 'line',
                        data: [{{ $countAcaIdeaMonth1 }}, {{ $countAcaIdeaMonth2 }},
                            {{ $countAcaIdeaMonth3 }},
                            {{ $countAcaIdeaMonth4 }},
                            {{ $countAcaIdeaMonth5 }}, {{ $countAcaIdeaMonth6 }},
                            {{ $countAcaIdeaMonth7 }},
                            {{ $countAcaIdeaMonth8 }},
                            {{ $countAcaIdeaMonth9 }}, {{ $countAcaIdeaMonth10 }},
                            {{ $countAcaIdeaMonth11 }},
                            {{ $countAcaIdeaMonth12 }},
                        ],
                        order: 1,
                    }, {
                        label: 'Support department',
                        data: [{{ $countSupIdeaMonth1 }}, {{ $countSupIdeaMonth2 }},
                            {{ $countSupIdeaMonth3 }},
                            {{ $countSupIdeaMonth4 }},
                            {{ $countSupIdeaMonth5 }}, {{ $countSupIdeaMonth6 }},
                            {{ $countSupIdeaMonth7 }},
                            {{ $countSupIdeaMonth8 }},
                            {{ $countSupIdeaMonth9 }}, {{ $countSupIdeaMonth10 }},
                            {{ $countSupIdeaMonth11 }},
                            {{ $countSupIdeaMonth12 }},
                        ],
                        type: 'line',
                        order: 1,
                    }],
                    labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September',
                        'October', 'November', 'December'
                    ]
                },
                options: {
                    plugins: {
                        resposive: true,
                        legend: {
                            display: false,
                        }
                    }
                }
            });
        </script>
    @endif
    <script script type="text/javascript">
        $('.show_delete').click(function(event) {
            var form = $(this).closest("form");
            var name = $(this).data("name");
            event.preventDefault();
            Swal.fire({
                title: 'Are you sure ?',
                text: 'Are you sure to delete this idea ?',
                icon: 'question',
                showCancelButton: true,
                scrollbarPadding: false,
                allowOutsideClick: false,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    </script>
@endsection
