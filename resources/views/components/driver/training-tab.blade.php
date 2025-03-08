@props(['oldData' => []])

<div class="bg-white p-4 rounded-lg shadow" x-data="{
    hasAttendedTrainingSchool: false,
    trainingSchools: [{
        id: null,
        date_start: '',
        date_end: '',
        school_name: '',
        city: '',
        state: '',
        phone_number: '',
        graduated: false,
        subject_to_safety_regulations: false,
        performed_safety_functions: false,
        training_skills: [],
    }],
    
    trainingOptions: [
        { value: 'double_trailer', label: 'Double Trailer' },
        { value: 'passenger', label: 'Passenger' },
        { value: 'tank_vehicle', label: 'Tank Vehicle' },
        { value: 'hazardous_material', label: 'Hazardous Material' },
        { value: 'combination_vehicle', label: 'Combination Vehicle' },
        { value: 'air_brakes', label: 'Air Brakes' },
    ],
    
    addTrainingSchool() {
        this.trainingSchools.push({
            id: null,
            date_start: '',
            date_end: '',
            school_name: '',
            city: '',
            state: '',
            phone_number: '',
            graduated: false,
            subject_to_safety_regulations: false,
            performed_safety_functions: false,
            training_skills: [],
        });
    },
    
    removeTrainingSchool(index) {
        if (this.trainingSchools.length > 1) {
            this.trainingSchools.splice(index, 1);
        }
    },
    
    toggleSkill(schoolIndex, skill) {
        const school = this.trainingSchools[schoolIndex];
        if (!school.training_skills) {
            school.training_skills = [];
        }
        
        const index = school.training_skills.indexOf(skill);
        if (index === -1) {
            school.training_skills.push(skill);
        } else {
            school.training_skills.splice(index, 1);
        }
    },
    
    hasSkill(schoolIndex, skill) {
        const school = this.trainingSchools[schoolIndex];
        if (!school.training_skills) {
            return false;
        }
        return school.training_skills.includes(skill);
    }
}">
    <h3 class="text-lg font-semibold mb-4">Commercial Driver Training Schools</h3>
    
    <div class="flex items-center mb-4">
        <x-base.form-check.input class="mr-2.5 border" type="checkbox"
            name="has_attended_training_school" value="1"
            x-model="hasAttendedTrainingSchool" />
        <span class="cursor-pointer select-none">
            Have you attended a commercial driver training school?
        </span>
    </div>
    
    <div x-show="hasAttendedTrainingSchool" x-transition>
        @verbatim
        <template x-for="(school, index) in trainingSchools" :key="index">
            <div class="border p-4 rounded-lg mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h4 class="font-medium" x-text="`Training School #${index + 1}`"></h4>
                    <button type="button" @click="removeTrainingSchool(index)"
                        class="text-red-500 text-sm" x-show="trainingSchools.length > 1">
                        <i class="fas fa-trash mr-1"></i> Remove
                    </button>
                </div>
                
                <input type="hidden" :name="`training_schools[${index}][id]`" :value="school.id">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">School Name</label>
                        <input type="text"
                            x-model="school.school_name"
                            :name="`training_schools[${index}][school_name]`"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                            placeholder="Name of school">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Phone Number</label>
                        <input type="text"
                            x-model="school.phone_number"
                            :name="`training_schools[${index}][phone_number]`"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                            placeholder="(555) 555-5555">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">City</label>
                        <input type="text"
                            x-model="school.city"
                            :name="`training_schools[${index}][city]`"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                            placeholder="City">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">State</label>
                        <input type="text"
                            x-model="school.state"
                            :name="`training_schools[${index}][state]`"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                            placeholder="State">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Start Date</label>
                        <input type="date"
                            x-model="school.date_start"
                            :name="`training_schools[${index}][date_start]`"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">End Date</label>
                        <input type="date"
                            x-model="school.date_end"
                            :name="`training_schools[${index}][date_end]`"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="flex items-center mb-2">
                        <input class="mr-2.5 border" type="checkbox"
                            x-model="school.graduated"
                            :name="`training_schools[${index}][graduated]`" 
                            value="1" />
                        <span class="cursor-pointer select-none">
                            Did you graduate from this program?
                        </span>
                    </div>
                    
                    <div class="flex items-center mb-2">
                        <input class="mr-2.5 border" type="checkbox"
                            x-model="school.subject_to_safety_regulations"
                            :name="`training_schools[${index}][subject_to_safety_regulations]`" 
                            value="1" />
                        <span class="cursor-pointer select-none">
                            Was this position subject to Federal Motor Carrier Safety Regulations?
                        </span>
                    </div>
                    
                    <div class="flex items-center mb-2">
                        <input class="mr-2.5 border" type="checkbox"
                            x-model="school.performed_safety_functions"
                            :name="`training_schools[${index}][performed_safety_functions]`" 
                            value="1" />
                        <span class="cursor-pointer select-none">
                            Did this job require you to perform safety-sensitive functions?
                        </span>
                    </div>
                </div>
                
                <div class="mb-2">
                    <label class="block text-sm font-medium mb-2">Which of the following skills were trained in your program? (select all that apply)</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                        <template x-for="option in trainingOptions" :key="option.value">
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox"
                                        :checked="hasSkill(index, option.value)"
                                        @click="toggleSkill(index, option.value)"
                                        class="mr-2 border-slate-200">
                                    <input type="hidden" 
                                        :name="`training_schools[${index}][training_skills][]`" 
                                        :value="option.value"
                                        x-show="hasSkill(index, option.value)">
                                    <span x-text="option.label"></span>
                                </label>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>
        @endverbatim
        
        <button type="button" @click="addTrainingSchool"
            class="btn btn-outline-primary mt-2">
            <i class="fas fa-plus mr-1"></i> Add Another Training School
        </button>
    </div>
</div>