<div>
    <!-- Order your soul. Reduce your wants. - Augustine -->
</div><div class="bg-white p-4 rounded-lg shadow" x-data="{
    hasTrafficConvictions: false,
    trafficConvictions: [{
        id: null,
        conviction_date: '',
        location: '',
        charge: '',
        penalty: '',
    }],
    
    addTrafficConviction() {
        this.trafficConvictions.push({
            id: null,
            conviction_date: '',
            location: '',
            charge: '',
            penalty: '',
        });
    },
    
    removeTrafficConviction(index) {
        if (this.trafficConvictions.length > 1) {
            this.trafficConvictions.splice(index, 1);
        }
    }
}">
    <h3 class="text-lg font-semibold mb-4">Traffic Convictions</h3>
    
    <div class="flex items-center mb-4">
        <x-base.form-check.input class="mr-2.5 border" type="checkbox"
            name="has_traffic_convictions" value="1"
            x-model="hasTrafficConvictions" />
        <span class="cursor-pointer select-none">
            Have you had any traffic violation convictions or forfeitures (other than parking violations) in the past three years prior to the application date?
        </span>
    </div>
    
    <div x-show="hasTrafficConvictions" x-transition>
        <template x-for="(conviction, index) in trafficConvictions" :key="index">
            <div class="border p-4 rounded-lg mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h4 class="font-medium" x-text="`Conviction #${index + 1}`"></h4>
                    <button type="button" @click="removeTrafficConviction(index)"
                        class="text-red-500 text-sm" x-show="trafficConvictions.length > 1">
                        <i class="fas fa-trash mr-1"></i> Remove
                    </button>
                </div>
                
                <input type="hidden" :name="`traffic_convictions[${index}][id]`" :value="conviction.id">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Conviction Date</label>
                        <input type="date"
                            x-model="conviction.conviction_date"
                            :name="`traffic_convictions[${index}][conviction_date]`"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Location</label>
                        <input type="text"
                            x-model="conviction.location"
                            :name="`traffic_convictions[${index}][location]`"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                            placeholder="City, State">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Charge</label>
                        <input type="text"
                            x-model="conviction.charge"
                            :name="`traffic_convictions[${index}][charge]`"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                            placeholder="Violation charged">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Penalty</label>
                        <input type="text"
                            x-model="conviction.penalty"
                            :name="`traffic_convictions[${index}][penalty]`"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                            placeholder="Fine, points, etc.">
                    </div>
                </div>
            </div>
        </template>
        
        <button type="button" @click="addTrafficConviction"
            class="btn btn-outline-primary mt-2">
            <i class="fas fa-plus mr-1"></i> Add Another Conviction
        </button>
    </div>
</div>