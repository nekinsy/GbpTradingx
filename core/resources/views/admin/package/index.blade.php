@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                                <tr>
                                    <th>@lang('Plan Name')</th>
                                    <th>@lang('Description')</th>
                                    <th>@lang('Price')</th>
                                    <th>@lang('Plan Type')</th>
                                    <th>@lang('Max Income') ({{ __($general->cur_text) }})</th>
                                    <th>@lang('Start Income')(%)
                                    <th>@lang('Rising Income')(%)</th>
                                    <th>@lang('Duration')</th>
                                    <th>@lang('Status')</th>
                                    <th style = "text-align : center">@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($packages as $key => $package)
                                    <tr>
                                        <td>{{ __($package->name) }}</td>
                                        <td>{{ __($package->description) }}</td>
                                        <td>{{ showAmount($package->price) }} {{ __($general->cur_text) }}</td>
                                        <td> @php echo $package->typeBadge @endphp</td>

                                        <td>
                                            {{ showAmount($package->max_income) }} {{ __($general->cur_text) }}
                                        </td>
                                        <td>
                                            {{ showAmount($package->start_income) }}
                                        </td>
                                        <td>
                                            {{ showAmount($package->rising_income) }}
                                        </td>
                                        <td>
                                            {{ $package->duration }} {{$package->repeat_unit}}
                                        </td>
                                        <td>
                                            @php echo $package->statusBadge @endphp
                                        </td>

                                        <td style = "text-align : center">
                                            <button class="btn btn-sm btn-outline--primary edit" data-toggle="tooltip"
                                                data-id="{{ $package->id }}" data-name="{{ $package->name }}"
                                                data-description="{{$package->description}}" data-price="{{$package->price}}"
                                                data-returning_price="{{ $package->returning_price }}"
                                                data-bonus_price="{{ $package->bonus_price }}"
                                                data-invite_count="{{ getAmount($package->invite_count) }}"
                                                data-invite_bonus="{{ $package->invite_bonus }}"
                                                data-type="{{ $package->type }}"
                                                data-max_income="{{$package->max_income}}" data-start_income = "{{$package->start_income}}" data-rising_income="{{$package->rising_income}}" data-duration="{{$package->duration}}" data-duration_unit="{{$package->repeat_unit}}"
                                                data-weekly_fee="{{$package->weekly_fee}}"
                                                data-original-title="@lang('Edit')" type="button">
                                                <i class="la la-pencil"></i> @lang('Edit')
                                            </button>

                                            @if ($package->status == Status::DISABLE)
                                                <button class="btn btn-sm btn-outline--success ms-1 confirmationBtn"
                                                    data-question="@lang('Are you sure to enable this package?')"
                                                    data-action="{{ route('admin.package.status', $package->id) }}">
                                                    <i class="la la-eye"></i> @lang('Enable')
                                                </button>
                                            @else
                                                <button class="btn btn-sm btn-outline--warning ms-1 confirmationBtn"
                                                    data-question="@lang('Are you sure to disable this package?')"
                                                    data-action="{{ route('admin.package.status', $package->id) }}">
                                                    <i class="la la-eye-slash"></i> @lang('Disable')
                                                </button>
                                            @endif
                                            <button class="btn btn-sm btn-outline--danger ms-1 confirmationBtn"
                                                data-question="@lang('Are you sure to delete this package?')"
                                                data-action="{{ route('admin.package.delete', $package->id) }}">
                                                <i class="las la-trash"></i> @lang('Delete')
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse

                            </tbody>
                        </table><!-- table end -->
                    </div>
                </div>
                @if ($packages->hasPages())
                    <div class="card-footer py-4">
                        @php echo paginateLinks($packages) @endphp
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- ConfirmationModal -->
    <div class="modal fade" id="confirmationModal" role="dialog" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Confirmation Alert!')</h5>
                    <button class="close" data-bs-dismiss="modal" type="button" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="question"></p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn--dark btn--sm" data-bs-dismiss="modal"
                            type="button">@lang('No')</button>
                        <button class="btn btn--base btn--sm btn--primary" type="submit">@lang('Yes')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Package modal --}}
    <div class="modal fade" id="edit-package" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Edit Investment Plan')</h5>
                    <button class="close" data-bs-dismiss="modal" type="button" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form method="post" action="{{ route('admin.package.save') }}">
                    @csrf
                    <div class="modal-body">

                        <input class="form-control package_id_edit" name="id" type="hidden">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>@lang('Plan Name')</label>
                                <input class="form-control name_edit" name="name" type="text" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>@lang('Price') </label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ $general->cur_sym }}</span>
                                    <input class="form-control price price_edit" name="price" type="numeric"
                                        step="any" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>@lang('Network Bonus on Plan Profit')(%)</label>
                                <input class = "form-control bonus bonus_price_edit" name = "bonus_price" type = "numeric" />
                            </div>
                            <div class="form-group col-md-6">
                                <label>@lang('Brokerage Fee (Weekly)')(%)</label>
                                <input class = "form-control weekly_fee weekly_fee_edit" name = "weekly_fee" type = "numeric" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label>@lang('Max Income') ({{ $general->cur_sym }})</label>
                                <input class = "form-control max_income_edit" name="max_income" id="max_income_edit" type="numeric" />
                            </div>
                            <div class="form-group col-md-4">
                                <label>@lang('Start Income') (%)</label>
                                <input class = "form-control start_income_edit" name="start_income" id="start_income_edit" type="number" />
                            </div>
                            <div class="form-group col-md-4">
                                <label>@lang('Rising Income') (%)</label>
                                <input class = "form-control rising_income_edit" name="rising_income" id="rising_income_edit" type="number" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>@lang('Duration')</label>
                                <input class="form-control duration_edit" name="duration" id = "duration_edit" type="number"/>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>@lang('Duration Unit')</label>
                                <select class="form-select select_unit_edit" name="select_unit" id="select_unit_edit">
                                    <option value = "">@lang('Select duration unit')</option>
                                        <option value="hour">Hour</option>
                                        <option value="day">Day</option>
                                </select>
                            </div>
                        </div>                        
                        <div class="form-group">
                            <label>@lang('Description')</label>
                            <textarea class="description_edit"  name="description" id="description_edit" cols="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn--primary w-100 h-45" type="submit">@lang('Add')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Add New Package -->
    <div class="modal fade" id="add-package" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Add Investment Plan')</h5>
                    <button class="close" data-bs-dismiss="modal" type="button" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form method="post" action="{{ route('admin.package.save') }}">
                    @csrf
                    <div class="modal-body">

                        <input class="form-control package_id" name="id" type="hidden">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>@lang('Plan Name')</label>
                                <input class="form-control name" name="name" type="text" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>@lang('Price') </label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ $general->cur_sym }}</span>
                                    <input class="form-control price price_ctrl" name="price" type="numeric"
                                        step="any" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>@lang('Network Bonus on Plan Profit')(%)</label>
                                <input class = "form-control bonus bonus_price" name = "bonus_price" type = "numeric" />
                            </div>
                            <div class="form-group col-md-6">
                                <label>@lang('Brokerage Fee (Weekly)')(%)</label>
                                <input class = "form-control weekly_fee" name = "weekly_fee" type = "numeric" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label>@lang('Max Income') ({{ $general->cur_sym }})</label>
                                <input class = "form-control max_income" name="max_income" id="max_income" type="number" />
                            </div>
                            <div class="form-group col-md-4">
                                <label>@lang('Start Income') (%)</label>
                                <input class = "form-control start_income" name="start_income" id="start_income" type="number" />
                            </div>
                            <div class="form-group col-md-4">
                                <label>@lang('Rising Income') (%)</label>
                                <input class = "form-control rising_income" name="rising_income" id="rising_income" type="number" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>@lang('Duration')</label>
                                <input class="form-control duration" name="duration" id = "duration" type="number"/>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>@lang('Duration Unit')</label>
                                <select class="form-select select_unit" name="select_unit" id="select_unit">
                                    <option value = "">@lang('Select duration unit')</option>
                                        <option value="hour">Hour</option>
                                        <option value="day">Day</option>
                                </select>
                            </div>
                        </div>                       
                        <div class="form-group">
                            <label>@lang('Description')</label>
                            <textarea name="description" id="description" cols="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn--primary w-100 h-45" type="submit">@lang('Save')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <button class="btn btn-sm btn-outline--primary add-package" type="button">
        <i class="la la-plus"></i>@lang('Add New')
    </button>
@endpush

@push('script')
    <script>
        "use strict";
        (function($) {
            $('.edit').on('click', function() {
                var modal = $('#edit-package');
                modal.find('.package_id_edit').val($(this).data('id'));
                modal.find('.name_edit').val($(this).data('name'));
                modal.find('.price_edit').val($(this).data('price'));
                modal.find('.returning_price_edit').val($(this).data('returning_price'));
                modal.find('.bonus_price_edit').val($(this).data('bonus_price'));
                modal.find('.max_income_edit').val($(this).data('max_income'));
                modal.find('.start_income_edit').val($(this).data('start_income'));
                modal.find('.rising_income_edit').val($(this).data('rising_income'));
                modal.find('.duration_edit').val($(this).data('duration'));
                modal.find('.invite_count_edit').val($(this).data('invite_count'));
                modal.find('.description_edit').val($(this).data('description'));
                modal.find('.invite_bonus_edit').val($(this).data('invite_bonus'));
                modal.find('.weekly_fee_edit').val($(this).data('weekly_fee'))
                // modal.find('.select_plan_edit').val($(this).data('plan_id'));
                modal.find('.select_unit_edit').val($(this).data('duration_unit'));
                // var type = $(this).data('package_type');
                // if (type == 1) {
                //     modal.find('.package_type_imme_edit').prop('checked', true);
                //     modal.find('.package_type_non_imme_edit').prop('checked', false);
                // } else if (type == 2) {
                //     modal.find('.package_type_imme_edit').prop('checked', false);
                //     modal.find('.package_type_non_imme_edit').prop('checked', true);
                // }
                modal.modal('show');
            });

            $('.add-package').on('click', function() {
                var modal = $('#add-package');
                modal.modal('show');
            });

            $('.confirmationBtn').on('click', function() {
                var modal = $('#confirmationModal');
                modal.modal('show');
            })

            // function calculateDistribution() {
            //     // Get the values of price and fee
            //     var price = parseFloat($('.price_ctrl').val()) || 0;
            //     var fee = parseFloat($('.fee_ctrl').val()) || 0;
            //     var size =parseInt($('.size_ctrl').val()) || 3;

            //     // Calculate distribution using the given formula
            //     var distribution = (price - fee / 100 * price) / size;

            //     // Set the distribution value in the input field
            //     $('.distribution_ctrl').val(distribution.toFixed(2)); // toFixed(2) keeps two decimal places
            // }

            // // Attach event listeners to the price and fee input fields
            // $('.price_ctrl, .fee_ctrl').on('input', calculateDistribution);
            // $('.size_ctrl').on('change', calculateDistribution);

            // // Initial calculation in case there are pre-filled values
            // calculateDistribution();

            // function calculateDistribution_edit() {
            //     // Get the values of price and fee
            //     var price = parseFloat($('.price_ctrl_edit').val()) || 0;
            //     var fee = parseFloat($('.fee_ctrl_edit').val()) || 0;
            //     var size =parseInt($('.size_ctrl_edit').val()) || 3;

            //     // Calculate distribution using the given formula
            //     var distribution = (price - fee / 100 * price) / size;

            //     // Set the distribution value in the input field
            //     $('.distribution_ctrl_edit').val(distribution.toFixed(2)); // toFixed(2) keeps two decimal places
            // }

            // // Attach event listeners to the price and fee input fields
            // $('.price_ctrl_edit, .fee_ctrl_edit').on('input', calculateDistribution_edit);
            // $('.size_ctrl_edit').on('change', calculateDistribution_edit);

            // Initial calculation in case there are pre-filled values
        })(jQuery);
    </script>
@endpush
