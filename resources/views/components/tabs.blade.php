<div class="tabs-group">
    <!-- Radio Inputs -->
    @foreach ($tabs as $id => $tab)
        <input type="radio"
               id="tab-{{ $id }}"
               name="tabs"
               class="sr-only"
            {{ $loop->first ? 'checked' : '' }}>
    @endforeach

    <!-- Tab Navigation -->
    <nav class="flex space-x-1 p-1 rounded-t-lg bg-amber-50/50">
        @foreach ($tabs as $id => $tab)
            <label for="tab-{{ $id }}"
                   class="tab-label cursor-pointer px-6 py-3 text-sm font-medium
                          text-gray-600 hover:text-amber-700
                          border border-transparent
                          rounded-t-lg transition-all duration-300 ease-in-out
                          hover:bg-white/50">
                {{ $tab['label'] }}
            </label>
        @endforeach
    </nav>

    <!-- Tab Content -->
    <div class="tab-contents border border-amber-200 rounded-b-lg rounded-tr-lg shadow-sm bg-white">
        @foreach ($tabs as $id => $tab)
            <div class="tab-panel hidden p-6 text-gray-700">
                {!! $tab['content'] !!}
            </div>
        @endforeach
    </div>
</div>

<style>
    /* Show content when radio is checked */
    @foreach ($tabs as $id => $tab)
        #tab-{{ $id }}:checked ~ .tab-contents .tab-panel:nth-child({{ $loop->iteration }}) {
        display: block;
        animation: fadeIn 300ms ease-in-out;
    }

    #tab-{{ $id }}:checked ~ nav .tab-label:nth-child({{ $loop->iteration }}) {
        color: #b45309;
        background: white;
        border-color: #fcd34d;
        border-bottom-color: transparent;
        box-shadow: 0 2px 4px -2px rgba(251, 191, 36, 0.2);
        position: relative;
        z-index: 1;
    }

    #tab-{{ $id }}:checked ~ nav .tab-label:nth-child({{ $loop->iteration }}):after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 2px;
        background: white;
    }
    @endforeach

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
