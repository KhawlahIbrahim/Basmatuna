<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreParticipationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) config('basmah.submissions_open');
    }

    protected function prepareForValidation(): void
    {
        $clean = fn ($v) => is_string($v)
            ? trim(preg_replace('/\s+/u', ' ', strip_tags($v)))
            : $v;

        $this->merge([
            'full_name' => $clean($this->input('full_name')),
            'audience_type' => $clean($this->input('audience_type')),
            'department' => $clean($this->input('department')),
            'department_other' => $clean($this->input('department_other')),
            'selected_word' => $clean($this->input('selected_word')),
            'other_word' => $clean($this->input('other_word')),
            // العبارة: نحافظ على سطر واحد وننظف الوسوم
            'message' => $clean($this->input('message')),
        ]);
    }

    public function rules(): array
    {
        $other = config('basmah.other_department');
        $otherWord = config('basmah.other_word');
        $photo = config('basmah.photo');
        $letters = '/^[\p{Arabic}\p{Latin}\s\.\-\'’ـ]+$/u';

        return [
            'full_name' => ['required', 'string', 'min:3', 'max:'.config('basmah.name_max'), 'regex:'.$letters],
            'audience_type' => ['required', 'string', Rule::in(array_keys(config('basmah.audience_types')))],
            'department' => ['required', 'string', Rule::in([...config('basmah.departments'), $other])],
            'department_other' => ['nullable', 'required_if:department,'.$other, 'string', 'min:2', 'max:80', 'regex:/^[\p{Arabic}\p{Latin}\p{N}\s\.\-\/()،,]+$/u'],
            'selected_word' => ['required', 'string', Rule::in([...config('basmah.words'), $otherWord])],
            'other_word' => ['nullable', 'required_if:selected_word,'.$otherWord, 'string', 'min:2', 'max:'.config('basmah.custom_word_max'), 'regex:/^[\p{Arabic}\s]+$/u'],
            'message' => ['required', 'string', 'min:'.config('basmah.message_min'), 'max:'.config('basmah.message_max')],
            'photo' => [
                'nullable', 'file', 'image',
                'mimes:jpg,jpeg,png,webp',
                'mimetypes:'.implode(',', $photo['mimes']),
                'max:'.$photo['max_kb'],
                'dimensions:min_width=120,min_height=120,max_width=8000,max_height=8000',
            ],
            'show_image' => ['nullable', 'boolean'],
            'consent' => ['accepted'],
            'website' => ['nullable', 'max:0'], // مصيدة للبرامج الآلية (حقل مخفي)
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'اكتب اسمك الكامل.',
            'full_name.min' => 'الاسم قصير جدًا.',
            'full_name.max' => 'الاسم أطول من :max حرفًا.',
            'full_name.regex' => 'يقبل الاسم الحروف العربية أو الإنجليزية فقط.',
            'audience_type.required' => 'اختر منسوب أو طالب/ة.',
            'audience_type.in' => 'اختر منسوب أو طالب/ة.',
            'department.required' => 'اختر القسم أو الوحدة.',
            'department.in' => 'اختر القسم من القائمة.',
            'department_other.required_if' => 'اكتب اسم القسم أو الوحدة.',
            'department_other.*' => 'اكتب اسم القسم بحروف واضحة (حتى 80 حرفًا).',
            'selected_word.required' => 'اختر كلمة تعبّر عن وطنك.',
            'selected_word.in' => 'اختر كلمة من القائمة.',
            'other_word.required_if' => 'اكتب كلمتك.',
            'other_word.regex' => 'اكتب الكلمة بالحروف العربية.',
            'other_word.max' => 'الكلمة أطول من :max حرفًا.',
            'other_word.min' => 'الكلمة قصيرة جدًا.',
            'message.required' => 'اكتب عبارتك.',
            'message.min' => 'العبارة قصيرة جدًا.',
            'message.max' => 'العبارة أطول من :max حرفًا.',
            'photo.image' => 'تعذّر رفع الصورة. جرّب صورة أخرى.',
            'photo.mimes' => 'تعذّر رفع الصورة. جرّب صورة أخرى.',
            'photo.mimetypes' => 'تعذّر رفع الصورة. جرّب صورة أخرى.',
            'photo.max' => 'تعذّر رفع الصورة. جرّب صورة أخرى.',
            'photo.dimensions' => 'تعذّر رفع الصورة. جرّب صورة أخرى.',
            'photo.uploaded' => 'تعذّر رفع الصورة. جرّب صورة أخرى.',
            'consent.accepted' => 'يلزم الموافقة على عرض مشاركتك قبل الإرسال.',
            'website.max' => 'تعذّر إرسال النموذج.',
        ];
    }

    /** الكلمة النهائية التي تُحفظ */
    public function finalWord(): string
    {
        return $this->input('selected_word') === config('basmah.other_word')
            ? (string) $this->input('other_word')
            : (string) $this->input('selected_word');
    }

    public function finalDepartment(): string
    {
        return $this->input('department') === config('basmah.other_department')
            ? (string) $this->input('department_other')
            : (string) $this->input('department');
    }
}
