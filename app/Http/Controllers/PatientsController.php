<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use App\Responses;
use Illuminate\Support\Facades\DB;
use App\Models\Patients;
use App\Models\PatientsStatus;
use App\Models\PatientsRecords;


class PatientsController extends Controller
{
	public function index(Request $request)
    {
		try {
			$rulesInput = [
				'name' => 'nullable|string|max:45',
				'address' => 'nullable|string|max:45',
				'status' => 'nullable|string|max:45',
				'sort' => 'nullable|string|in:tanggal_masuk,tanggal_keluar,address',
				'order' => 'nullable|string|in:asc,desc',
			];

			// validasi input
			$isValidInput = Validator::make($request->all(), $rulesInput);
			if (!$isValidInput->passes()) {
				$apiResp = Responses::unprocessable_entity($isValidInput->messages()->first());
				return new Response($apiResp, $apiResp['code']);
			}

			$status = null;
			if ($request->input('status')) {
				$status = PatientsStatus::where('name', $request->input('status'))->first();
				if ($status === null) {
					$apiResp = Responses::unprocessable_entity('Invalid status');
					return new Response($apiResp, $apiResp['code']);
				}
			}

			$patients = Patients::with([
				'records' => function($query) use ($request) {
					$query->select('id', 'patient_id', 'in_date_at', 'out_date_at', 'status');
					if ($request->input('sort') == 'tanggal_masuk' && $request->input('order')) $query->orderBy('in_date_at', $request->input('order'));
					if ($request->input('sort') == 'tanggal_keluar' && $request->input('order')) $query->orderBy('out_date_at', $request->input('order'));
				},
				'records.status' => function($query) {
					$query->select('id', 'status', 'name');
				}
			])->select('id', 'name', 'phone', 'address');

			if ($request->input('name')) $patients->where('name', 'like', '%'.$request->input('name').'%');
			if ($request->input('address')) $patients->where('address', 'like', '%'.$request->input('address').'%');
			if ($status) {
				$patients->whereHas('records', function($query) use ($status) {
					$query->where('status', $status->status);
				});
			}
			if ($request->input('sort') == 'address' && $request->input('order')) $patients->orderBy('address', $request->input('order'));

			$patients = $patients->get();

			if (count($patients) < 1) {
				$apiResp = Responses::no_content_to_send();
				return (new Response($apiResp, $apiResp['code']));	
			}
			
			$apiResp = Responses::success('success get all patients');
			$apiResp['data'] = [
				'patients' => $patients,
			];
			return (new Response($apiResp, $apiResp['code']));
		} catch (\Exception $e) {
			$apiResp = Responses::error($e->getMessage());
			return (new Response($apiResp, $apiResp['code']));
		}
    }

	public function store(Request $request)
    {
		try {
			$rulesInput = [
				'name' => 'required|string|max:45',
				'phone' => 'required|string|max:45',
				'address' => 'required|string|max:45',
				'status' => 'required|string|max:45',
				'in_date_at' => 'nullable|date_format:Y-m-d',
				'out_date_at' => 'nullable|date_format:Y-m-d',
			];

			// validasi input
			$isValidInput = Validator::make($request->all(), $rulesInput);
			if (!$isValidInput->passes()) {
				$apiResp = Responses::unprocessable_entity($isValidInput->messages()->first());
				return new Response($apiResp, $apiResp['code']);
			}

			$status = PatientsStatus::where('name', $request->input('status'))->first();
			if ($status === null) {
				$apiResp = Responses::unprocessable_entity('Invalid status');
				return new Response($apiResp, $apiResp['code']);
			}

			// check existing patient
			$patientExistByPhone = Patients::where('phone', $request->input('phone'))->withTrashed()->first();
			// if ($patientExistByPhone !== null) {
			// 	$apiResp = Responses::bad_request('Phone number had been used');
			// 	return new Response($apiResp, $apiResp['code']);
			// }

			DB::beginTransaction();
			if ($patientExistByPhone === null) {
				$patient = Patients::create([
					'name' => $request->input('name'),
					'phone' => $request->input('phone'),
					'address' => $request->input('address'),
				]);
			} else {
				$patient = $patientExistByPhone;
			}

			$record = PatientsRecords::create([
				'patient_id' => $patient->id,
				'status' => $status->id,
				'in_date_at' => $request->input('in_date_at'),
				'out_date_at' => $request->input('out_date_at'),
			]);
			DB::commit();

			$record->status = $request->input('status');
			$patient->record = $record;
			
			$apiResp = Responses::success('success get all patients');
			$apiResp['data'] = [
				'patient' => $patient,
			];
			return (new Response($apiResp, $apiResp['code']));
		} catch (\Exception $e) {
			DB::rollBack();
			$apiResp = Responses::error($e->getMessage());
			return (new Response($apiResp, $apiResp['code']));
		}
    }

	public function show(Request $request, $id)
    {
		try {
			$request->merge(['id' => $id]);
			$rulesInput = [
				'id' => 'required|integer',
			];

			// validasi input
			$isValidInput = Validator::make($request->all(), $rulesInput);
			if (!$isValidInput->passes()) {
				$apiResp = Responses::unprocessable_entity($isValidInput->messages()->first());
				return new Response($apiResp, $apiResp['code']);
			}

			$patient = Patients::with([
				'records' => function($query) {
					$query->select('id', 'patient_id', 'in_date_at', 'out_date_at', 'status');
				},
				'records.status' => function($query) {
					$query->select('id', 'status', 'name');
				}
			])->select('id', 'name', 'phone', 'address')->where('id', $id)->get();

			if (count($patient) < 1) {
				$apiResp = Responses::not_found('Patient not found');
				return new Response($apiResp, $apiResp['code']);
			}
			
			$apiResp = Responses::success('success get detail patient');
			$apiResp['data'] = [
				'patient' => $patient,
			];
			return (new Response($apiResp, $apiResp['code']));
		} catch (\Exception $e) {
			$apiResp = Responses::error($e->getMessage());
			return (new Response($apiResp, $apiResp['code']));
		}
    }

	public function update(Request $request, $id)
    {
		try {
			$request->merge(['id' => $id]);
			$rulesInput = [
				'id' => 'required|integer',
				'name' => 'nullable|string|max:45',
				'phone' => 'nullable|string|max:45',
				'address' => 'nullable|string|max:45',
				'status' => 'nullable|string|max:45',
				'in_date_at' => 'nullable|date_format:Y-m-d',
				'out_date_at' => 'nullable|date_format:Y-m-d',
			];

			// validasi input
			$isValidInput = Validator::make($request->all(), $rulesInput);
			if (!$isValidInput->passes()) {
				$apiResp = Responses::unprocessable_entity($isValidInput->messages()->first());
				return new Response($apiResp, $apiResp['code']);
			}

			$status = PatientsStatus::where('name', $request->input('status'))->first();

			// only update if not null
			// https://stackoverflow.com/questions/43632109/laravel-only-update-if-not-null

			DB::beginTransaction();
			$affectedPatient = Patients::where('id', $id)->update(array_filter([
				'name' => $request->input('name'),
				'phone' => $request->input('phone'),
				'address' => $request->input('address'),
			]));

			if ($affectedPatient < 1) {
				$apiResp = Responses::not_found('Patient not found');
				return new Response($apiResp, $apiResp['code']);
			}

			$affectedRecord = PatientsRecords::where('patient_id', $id)->update(array_filter([
				'status' => $status->id ?? null,
				'in_date_at' => $request->input('in_date_at'),
				'out_date_at' => $request->input('out_date_at'),
			]));
			DB::commit();

			$patient = Patients::with([
				'records' => function($query) {
					$query->select('id', 'patient_id', 'in_date_at', 'out_date_at', 'status');
				},
				'records.status' => function($query) {
					$query->select('id', 'status', 'name');
				}
			])->select('id', 'name', 'phone', 'address')->where('id', $id)->get();
			
			$apiResp = Responses::success('success update patient');
			$apiResp['data'] = [
				'patient' => $patient,
			];
			return (new Response($apiResp, $apiResp['code']));
		} catch (\Exception $e) {
			DB::rollBack();
			$apiResp = Responses::error($e->getMessage());
			return (new Response($apiResp, $apiResp['code']));
		}
    }

	public function destroy(Request $request, $id)
    {
		try {
			$request->merge(['id' => $id]);
			$rulesInput = [
				'id' => 'required|integer'
			];

			// validasi input
			$isValidInput = Validator::make($request->all(), $rulesInput);
			if (!$isValidInput->passes()) {
				$apiResp = Responses::unprocessable_entity($isValidInput->messages()->first());
				return new Response($apiResp, $apiResp['code']);
			}

			DB::beginTransaction();
			$affectedPatient = Patients::where('id', $id)->delete();
			if ($affectedPatient < 1) {
				DB::rollBack();
				$apiResp = Responses::not_found('Patient not found');
				return new Response($apiResp, $apiResp['code']);
			}
			DB::commit();
			
			$apiResp = Responses::no_content_to_send('success delete patient');
			return (new Response($apiResp, $apiResp['code']));
		} catch (\Exception $e) {
			DB::rollBack();
			$apiResp = Responses::error($e->getMessage());
			return (new Response($apiResp, $apiResp['code']));
		}
    }
}