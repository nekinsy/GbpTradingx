@php
    $planContent = getContent('plan.content', true);
    $plans = \App\Models\Package::where('status', Status::ENABLE)->get();
    $gatewayCurrency = \App\Models\GatewayCurrency::whereHas('method', function ($gate) {
        $gate->where('status', Status::ENABLE);
    })
        ->with('method')
        ->orderby('name')
        ->get();
@endphp
<section class="pricing-section padding-bottom padding-top">
    <div class="container">
        <div class="section-header">
            <h2 class="title">{{ __(@$planContent->data_values->heading) }}</h2>
            <p>{{ __(@$planContent->data_values->sub_heading) }}</p>
        </div>

        <div class="row justify-content-center mb-30-none">
            @foreach ($plans as $plan)
                <div class="col-lg-4 col-md-6 col-sm-10 mb-30">
                    <div class="plan-card bg_img text-center" data-background="{{ asset(getImage('assets/images/frontend/plan/' . @$planContent->data_values->background_image, '700x480')) }}">
                        <h4 class="plan-card__title text--base mb-2">{{ __(@$plan->name) }}</h4>
                        <div class="price-range mt-5 text-white"> {{ showAmount($plan->price) }}
                            {{ __($general->cur_text) }} </div>
                        <ul class="plan-card__features mt-4">
                            <li> @lang('Maxiumn Income on Plan') : <span class="amount">{{ $general->cur_sym }}{{ $plan->max_income }}</span>
                                <span class="icon float-right" data-bs-toggle="modal" data-bs-target="#profitInfoModal"><i
                                        class="fas fa-question-circle"></i></span>
                            </li>
                            <li>
                                @lang('Daily Income') : <span
                                    class="amount">{{ number_format($plan->start_income * $plan->price / 100, $plan->start_income * $plan->price / 100 - floor($plan->start_income * $plan->price / 100) == 0 ? 0 : 1, '.', '') }}  {{ __($general->cur_text) }}</span>
                                <span class="icon float-right" data-bs-toggle="modal" data-bs-target="#inviteCountModal"><i
                                        class="fas fa-question-circle"></i></span>
                            </li>

                            <li>
                                @lang('Brokerage Fee') : <span
                                    class="amount">{{ showAmount($plan->weekly_fee) }} (%)</span>
                                <span class="icon float-right" data-bs-toggle="modal" data-bs-target="#bonusAmoutModal"><i
                                        class="fas fa-question-circle"></i></span>
                            </li>
                            <li>
                                @lang('Duration') : <span
                                    class="amount">{{ intval($plan->duration) }} {{$plan->repeat_unit}}</span>
                                <span class="icon float-right" data-bs-toggle="modal" data-bs-target="#durationInfoModal"><i
                                        class="fas fa-question-circle"></i></span>
                            </li>
                            
                        </ul>

                        @auth
                            @if (@auth()->user()->plan->price > $plan->price)
                                <button class="custom-button theme disabled mt-3 w-auto text-white" type="button">
                                    @lang('Unavailable')
                                </button>
                            @elseif (auth()->user()->plan_id != $plan->id)
                                <button class="subscribeBtn custom-button theme mt-3 w-auto text-white" data-amount="{{ getAmount($plan->price) }}" data-id="{{ $plan->id }}" type="button">
                                    @lang('Subscribe Now')
                                </button>
                            @else
                                <button class="custom-button btn--success disabled mt-3 w-auto" type="button">
                                    @lang('Cureent Plan')
                                </button>
                            @endif
                        @else
                            <button class="custom-button theme mt-3 w-auto text-white" data-bs-toggle="modal" data-bs-target="#loginModal">
                                @lang('Subscribe now')
                            </button>
                        @endauth

                    </div>
                </div>
            @endforeach

        </div>

    </div>
</section>

@include($activeTemplate . 'partials.plan_modals')
