@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="dashboard-inner">
        <div class="mb-4">
            <div class="card custom--card">
                <div class="card-header">
                    <h5 class="text-center">@lang('Referrer Link')</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <form>
                                <div class="form-group">
                                    <label class="form-label">@lang('Join left')</label>
                                    <div class="copy-link">
                                        <input class="copyURL w-100" type="text"
                                            value="{{ route('home') }}/?ref={{ auth()->user()->username }}&position=left"
                                            readonly>
                                        <span class="copyBoard" id="copyBoard">
                                            <i class="las la-copy"></i>
                                            <strong class="copyText">@lang('Copy')</strong>
                                        </span>
                                    </div>
                                </div>
                            </form>
                        </div>
    
                        <div class="col-md-4">
                            <form>
                                <div class="form-group">
                                    <label class="form-label">@lang('Join Center')</label>
                                    <div class="copy-link">
                                        <input class="copyURL3 w-100" type="text"
                                            value="{{ route('home') }}/?ref={{ auth()->user()->username }}&position=center"
                                            readonly>
                                        <span class="copyBoard3" id="copyBoard3">
                                            <i class="las la-copy"></i>
                                            <strong class="copyText3">@lang('Copy')</strong>
                                        </span>
                                    </div>
                                </div>
                            </form>
                        </div>
    
                        <div class="col-md-4">
                            <form>
                                <div class="form-group">
                                    <label class="form-label">@lang('Join right')</label>
                                    <div class="copy-link">
                                        <input class="copyURL2 w-100" type="text"
                                            value="{{ route('home') }}/?ref={{ auth()->user()->username }}&position=right"
                                            readonly>
                                        <span class="copyBoard2" id="copyBoard2">
                                            <i class="las la-copy"></i>
                                            <strong class="copyText2">@lang('Copy')</strong>
                                        </span>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mb-4">
            <h3 class="mb-2">@lang('My Referrals')</h3>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card custom--card">
                    @if (!blank($logs))
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table--responsive--md table">
                                    <thead>
                                        <tr>
                                            <th>@lang('Username')</th>
                                            <th>@lang('Name')</th>
                                            <th>@lang('Email')</th>
                                            <th>@lang('Join Date')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($logs as $data)
                                            <tr>
                                                <td>{{ $data->username }}</td>
                                                <td>{{ $data->fullname }}</td>
                                                <td>{{ showEmailAddress($data->email) }}</td>
                                                <td>
                                                    @if ($data->created_at != '')
                                                        {{ showDateTime($data->created_at) }}
                                                    @else
                                                        @lang('Not Assign')
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="card-body text-center">
                            <h4 class="text--muted"><i class="far fa-frown"></i> {{ __($emptyMessage) }}</h4>
                        </div>
                    @endif
                    @if ($logs->hasPages())
                        <div class="card-footer py-4">
                            {{ paginateLinks($logs) }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        "use strict";
        (function ($){
            $('#copyBoard').click(function () {
                    var copyText = document.getElementsByClassName("copyURL");
                    copyText = copyText[0];
                    copyText.select();
                    copyText.setSelectionRange(0, 99999);

                    /*For mobile devices*/
                    document.execCommand("copy");
                    $('.copyText').text('Copied');
                    setTimeout(() => {
                        $('.copyText').text('Copy');
                    }, 2000);
                });
                $('#copyBoard2').click(function () {
                    var copyText = document.getElementsByClassName("copyURL2");
                    copyText = copyText[0];
                    copyText.select();
                    copyText.setSelectionRange(0, 99999);

                    /*For mobile devices*/
                    document.execCommand("copy");
                    $('.copyText2').text('Copied');
                    setTimeout(() => {
                        $('.copyText2').text('Copy');
                    }, 2000);
                });

                $('#copyBoard3').click(function () {
                    var copyText = document.getElementsByClassName("copyURL3");
                    copyText = copyText[0];
                    copyText.select();
                    copyText.setSelectionRange(0, 99999);

                    /*For mobile devices*/
                    document.execCommand("copy");
                    $('.copyText3').text('Copied');
                    setTimeout(() => {
                        $('.copyText3').text('Copy');
                    }, 2000);
                })
        })(jQuery);
    </script>
@endpush
