<div>
    <!-- Simplicity is the consequence of refined emotions. - Jean D'Alembert -->
</div><div class="bg-white p-4 " x-data="{
    hasAccidents: false,
    accidents: [{
        id: null,
        accident_date: '',
        nature_of_accident: '',
        had_injuries: false,
        number_of_injuries: 0,
        had_fatalities: false,
        number_of_fatalities: 0,
        comments: '',
    }],
    
    addAccident() {
        this.accidents.push({
            id: null,
            accident_date: '',
            nature_of_accident: '',
            had_injuries: false,
            number_of_injuries: 0,
            had_fatalities: false,
            number_of_fatalities: 0,
            comments: '',
        });
    },
    
    removeAccident(index) {
        if (this.accidents.length > 1) {
            this.accidents.splice(index, 1);
        }
    }
}">
    <h3 class="text-lg font-semibold mb-4">Accident Record</h3>
    
    <div class="flex items-center mb-4">
        <x-base.form-check.input class="mr-2.5 border" type="checkbox"
            name="has_accidents" value="1"
            x-model="hasAccidents" />
        <span class="cursor-pointer select-none">
            Have you had any accidents in the previous three years?
        </span>
    </div>
    
    <div x-show="hasAccidents" x-transition>
        <template x-for="(accident, index) in accidents" :key="index">
            <div class="border p-4 rounded-lg mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h4 class="font-medium" x-text="`Accident #${index + 1}`"></h4>
                    <button type="button" @click="removeAccident(index)"
                        class="text-red-500 text-sm" x-show="accidents.length > 1">
                        <i class="fas fa-trash mr-1"></i> Remove
                    </button>
                </div>
                
                <input type="hidden" :name="`accidents[${index}][id]`" :value="accident.id">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Accident Date</label>
                        <input type="date"
                            x-model="accident.accident_date"
                            :name="`accidents[${index}][accident_date]`"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Nature of Accident</label>
                        <input type="text"
                            x-model="accident.nature_of_accident"
                            :name="`accidents[${index}][nature_of_accident]`"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                            placeholder="Head-on, rear-end, etc.">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <div class="flex items-center mb-2">
                            <input type="checkbox"
                                x-model="accident.had_injuries"
                                :name="`accidents[${index}][had_injuries]`"
                                value="1"
                                class="mr-2 border-slate-200">
                            <span>Injuries</span>
                        </div>
                        <div x-show="accident.had_injuries">
                            <label class="block text-sm font-medium mb-1">Number of Injuries</label>
                            <input type="number"
                                x-model="accident.number_of_injuries"
                                :name="`accidents[${index}][number_of_injuries]`"
                                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                                min="0">
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center mb-2">
                            <input type="checkbox"
                                x-model="accident.had_fatalities"
                                :name="`accidents[${index}][had_fatalities]`"
                                value="1"
                                class="mr-2 border-slate-200">
                            <span>Fatalities</span>
                        </div>
                        <div x-show="accident.had_fatalities">
                            <label class="block text-sm font-medium mb-1">Number of Fatalities</label>
                            <input type="number"
                                x-model="accident.number_of_fatalities"
                                :name="`accidents[${index}][number_of_fatalities]`"
                                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                                min="0">
                        </div>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1">Comments</label>
                    <textarea
                        x-model="accident.comments"
                        :name="`accidents[${index}][comments]`"
                        class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                        rows="3"
                        placeholder="Additional details about the accident"></textarea>
                </div>
            </div>
        </template>
        
        <button type="button" @click="addAccident"
            class="border border-primary/50 px-4 py-2 rounded text-primary hover:text-white hover:bg-primary transition">
            <i class="fas fa-plus mr-1"></i> Add Another Accident
        </button>
    </div>
</div>