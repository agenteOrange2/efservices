@props(['oldData' => []])

<div class="bg-white p-4" x-data="trainingSchoolsComponent()">
    <h3 class="text-lg font-semibold mb-4">Commercial Driver Training Schools</h3>

    <div class="flex items-center mb-4">
        <x-base.form-check.input class="mr-2.5 border" type="checkbox" name="has_attended_training_school" value="1"
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
                        <button type="button" @click="removeTrainingSchool(index)" class="text-red-500 text-sm"
                            x-show="trainingSchools.length > 1">
                            <i class="fas fa-trash mr-1"></i> Remove
                        </button>
                    </div>

                    <input type="hidden" :name="`training_schools[${index}][id]`" :value="school.id">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">School Name</label>
                            <input type="text" x-model="school.school_name"
                                :name="`training_schools[${index}][school_name]`"
                                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                                placeholder="Name of school">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Phone Number</label>
                            <input type="text" x-model="school.phone_number"
                                :name="`training_schools[${index}][phone_number]`"
                                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                                placeholder="(555) 555-5555">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">City</label>
                            <input type="text" x-model="school.city" :name="`training_schools[${index}][city]`"
                                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3" placeholder="City">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">State</label>
                            <input type="text" x-model="school.state" :name="`training_schools[${index}][state]`"
                                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3" placeholder="State">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Start Date</label>
                            <input type="date" x-model="school.date_start"
                                :name="`training_schools[${index}][date_start]`"
                                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">End Date</label>
                            <input type="date" x-model="school.date_end" :name="`training_schools[${index}][date_end]`"
                                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                        </div>
                    </div>


                    <div class="mb-4">
                        <div class="flex items-center mb-2">
                            <input class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2" type="checkbox"
                                x-model="school.graduated" :name="`training_schools[${index}][graduated]`" value="1" />
                            <span class="cursor-pointer select-none">
                                Did you graduate from this program?
                            </span>
                        </div>

                        <div class="flex items-center mb-2">
                            <input class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2" type="checkbox"
                                x-model="school.subject_to_safety_regulations"
                                :name="`training_schools[${index}][subject_to_safety_regulations]`" value="1" />
                            <span class="cursor-pointer select-none">
                                Was this position subject to Federal Motor Carrier Safety Regulations?
                            </span>
                        </div>

                        <div class="flex items-center mb-2">
                            <input class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2" type="checkbox"
                                x-model="school.performed_safety_functions"
                                :name="`training_schools[${index}][performed_safety_functions]`" value="1" />
                            <span class="cursor-pointer select-none">
                                Did this job require you to perform safety-sensitive functions?
                            </span>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="block text-sm font-medium mb-2">Which of the following skills were trained in your
                            program? (select all that apply)</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                            <template x-for="option in trainingOptions" :key="option.value">
                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" :checked="hasSkill(index, option.value)"
                                            @click="toggleSkill(index, option.value)"
                                            class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2">
                                        <input type="hidden" :name="`training_schools[${index}][training_skills][]`"
                                            :value="option.value" x-show="hasSkill(index, option.value)">
                                        <span x-text="option.label"></span>
                                    </label>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Nueva sección para subir certificados con previsualización -->
                    <div class="mb-4  ">
                        <label class="block text-sm font-medium mb-1">School Certificates</label>
                        <div class="flex items-center mb-2 mt-8">
                            <input type="file" :id="`training_certificate_${index}`"
                                @change="onCertificateUpload($event, index)" class="hidden" multiple
                                accept=".pdf,.jpg,.jpeg,.png">
                            <label :for="`training_certificate_${index}`"
                                class="cursor-pointer bg-primary text-white px-3 py-2 rounded-md shadow-sm text-sm hover:bg-primary-dark">
                                <i class="fas fa-upload mr-2"></i> Upload Certificate(s)
                            </label>
                        </div>

                        <!-- Lista de certificados cargados con previsualización -->
                        <div class="mt-6 border-t border-slate-200/60 bg-slate-50 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                            <template x-for="(certFile, tokenIndex) in school.temp_certificate_tokens"
                                :key="tokenIndex">
                                <div class="flex flex-col bg-gray-50 p-2 rounded mb-1 relative">
                                    <!-- Miniatura de previsualización -->
                                    <div
                                        class="w-full h-32 mb-2 bg-gray-200 flex items-center justify-center rounded overflow-hidden">
                                        <!-- Si es una imagen, mostrar previsualización -->
                                        <template x-if="isImageFile(certFile.filename)">
                                            <img :src="certFile.preview_url || ''" class="object-contain h-full w-full"
                                                alt="Certificate preview">
                                        </template>

                                        <!-- Si es un PDF, mostrar icono -->
                                        <template x-if="isPdfFile(certFile.filename)">
                                            <div class="flex flex-col items-center justify-center">
                                                <i class="fas fa-file-pdf text-red-500 text-3xl mb-1"></i>
                                                <span class="text-xs text-gray-600">PDF Document</span>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Información del archivo -->
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1 overflow-hidden">
                                            <span class="text-sm truncate block" x-text="certFile.filename"></span>
                                        </div>
                                        <button type="button" @click="removeCertificate(index, tokenIndex)"
                                            class="text-red-500 hover:text-red-700 ml-2">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>

                                    <input type="hidden"
                                        :name="`training_schools[${index}][certificates][${tokenIndex}]`"
                                        :value="certFile.token">
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        @endverbatim

        <button type="button" @click="addTrainingSchool"
            class="border border-primary/50 px-4 py-2 rounded text-primary hover:text-white hover:bg-primary transition">
            <i class="fas fa-plus mr-1"></i> Add Another Training School
        </button>
    </div>
</div>

<script>
    function trainingSchoolsComponent() {
        return {
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
                temp_certificate_tokens: []
            }],

            trainingOptions: [{
                    value: 'double_trailer',
                    label: 'Double Trailer'
                },
                {
                    value: 'passenger',
                    label: 'Passenger'
                },
                {
                    value: 'tank_vehicle',
                    label: 'Tank Vehicle'
                },
                {
                    value: 'hazardous_material',
                    label: 'Hazardous Material'
                },
                {
                    value: 'combination_vehicle',
                    label: 'Combination Vehicle'
                },
                {
                    value: 'air_brakes',
                    label: 'Air Brakes'
                }
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
                    temp_certificate_tokens: []
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
            },

            // Verificar si el archivo es una imagen
            isImageFile(filename) {
                if (!filename) return false;
                const ext = filename.split('.').pop().toLowerCase();
                return ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext);
            },

            // Verificar si el archivo es un PDF
            isPdfFile(filename) {
                if (!filename) return false;
                const ext = filename.split('.').pop().toLowerCase();
                return ext === 'pdf';
            },

            async onCertificateUpload(event, schoolIndex) {
                const files = event.target.files;
                if (!files || files.length === 0) return;

                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('type', 'school_certificates');

                    try {
                        const response = await fetch('/admin/temp-upload', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content')
                            }
                        });

                        if (response.ok) {
                            const data = await response.json();

                            if (!this.trainingSchools[schoolIndex].temp_certificate_tokens) {
                                this.trainingSchools[schoolIndex].temp_certificate_tokens = [];
                            }

                            // Generar URL de vista previa para imágenes
                            let previewUrl = null;
                            if (this.isImageFile(file.name)) {
                                previewUrl = URL.createObjectURL(file);
                            }

                            this.trainingSchools[schoolIndex].temp_certificate_tokens.push({
                                token: data.token,
                                filename: file.name,
                                preview_url: previewUrl,
                                file_url: data.url || null
                            });
                        } else {
                            console.error('Error uploading file:', await response.text());
                        }
                    } catch (error) {
                        console.error('Error:', error);
                    }
                }
            },

            removeCertificate(schoolIndex, tokenIndex) {
                const certificate = this.trainingSchools[schoolIndex].temp_certificate_tokens[tokenIndex];

                // Liberar recursos de URL.createObjectURL si existe
                if (certificate.preview_url) {
                    URL.revokeObjectURL(certificate.preview_url);
                }

                this.trainingSchools[schoolIndex].temp_certificate_tokens.splice(tokenIndex, 1);
            }
        };
    }
</script>
