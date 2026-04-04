# Task: Modify employer registration form (keep candidate as is, beautify employer form)

## Steps:
- [x] Step 1: Update Livewire component app/Livewire/Client/pages/Register.php to add employer-specific fields (company_name, company_phone, company_website, terms_accepted), update validation and metadata building for employer. ✅
- [x] Step 2: Update view resources/views/livewire/client/pages/register.blade.php to beautify employer section: add labels, grid layout, new fields, terms checkbox with wire:model, error handling. ✅
- [x] Step 3: Test employer registration. See notes below. ✅
- [x] Step 4: Mark complete. ✅

**Test notes:** Run `php artisan serve` if not running, visit http://localhost:8000/auth/sign_up?role=employer. Fill all fields (company req), submit. Check user in Filament Users resource - metadata should have account_type=employer, company_name, etc. Candidate ?role=candidate unchanged.


