<?php

namespace DTApi\Http\Controllers;

use DTApi\Models\Job;
use DTApi\Http\Requests;
use DTApi\Models\Distance;
use Illuminate\Http\Request;
use DTApi\Repository\BaseRepository;
use Illuminate\Support\Facades\Validator;
use \Symfony\Component\HttpFoundation\Response;

/**
 * Class BookingController
 *
 * @package DTApi\Http\Controllers
 */
class BookingController extends Controller
{

    /**
     * @var BaseRepository
     */
    protected $repository;

    /**
     * BookingController constructor.
     *
     * @param BaseRepository $baseRepository
     */
    public function __construct(BaseRepository $baseRepository)
    {
        $this->repository = $baseRepository;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request)
    {
        // $response may not be initialized.
        $response = null;

        /*
         * better to check if Request has user_id
         */
        if ($request->has('user_id')) {
            $response = $this->repository->getUsersJobs($request->get('user_id'));
        } elseif ($request->__authenticatedUser->user_type == env('ADMIN_ROLE_ID') ||
            $request->__authenticatedUser->user_type == env('SUPERADMIN_ROLE_ID')) {
            /*
             * No need to pass entire $request object(until necessary).
             * Extract required information and pass to method.
             */
            $requestData = [
                'requestData' => $request->all(),
                'user' => $request->__authenticatedUser,
            ];
            $response = $this->repository->getAll($requestData);
        }

        return response($response);
    }

    /**
     * @param $id
     *
     * @return Response
     */
    public function show($id)
    {
        /*
         * A good approach is to validate the id first.
         * I am assuming id will be alpha numeric, not a regular id from database.
         * if it is regular integer id, validation rule will be:
         * 'id' => 'required|integer',
         */
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|alpha-num',
        ]);

        if ($validator->fails()) {
            Session::flash('error', $validator->messages()->first());
            return redirect()->back()->withInput();
        }

        $job = $this->repository->with('translatorJobRel.user')->find($id);

        return response($job);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $data = $request->all();

        $response = $this->repository->store($request->__authenticatedUser, $data);

        return response($response);

    }

    /**
     * @param         $id
     * @param Request $request
     *
     * @return Response
     */
    public function update($id, Request $request)
    {
        /*
         * There should be a validation rules class that can return validation rules on
         * the base of different scenarios.
         * Validate request's data first to make sure every thing is present and in the same shape in
         * which it should be
         */
        $data = $request->all();
        $consumer = $request->__authenticatedUser;
        $response = $this->repository->updateJob($id, array_except($data, ['_token', 'submit']), $consumer);

        return response($response);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function immediateJobEmail(Request $request)
    {
        /**
         * There is no point to load admin email here.
         * also, key should be app.adminEmail instead of app.adminemail
         */

        // $adminSenderEmail = config('app.adminemail');

        $data = $request->all();
        /*
         * Validate data first.
         */
        $response = $this->repository->storeJobEmail($data);

        return response($response);
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function getHistory(Request $request)
    {
        if ($user_id = $request->get('user_id')) {
            /**
             * validate request. and send only data, not the entire request object.
             */
            $requestData = $request->all();
            $response = $this->repository->getUsersJobsHistory($user_id, $requestData);
            return response($response);
        }

        return null;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function acceptJob(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->repository->acceptJob($data, $user);

        return response($response);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function acceptJobWithId(Request $request)
    {
        $data = $request->get('job_id');
        $user = $request->__authenticatedUser;

        $response = $this->repository->acceptJobWithId($data, $user);

        return response($response);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function cancelJob(Request $request)
    {
        // validation.
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->repository->cancelJobAjax($data, $user);

        return response($response);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function endJob(Request $request)
    {
        // validation
        $data = $request->all();

        $response = $this->repository->endJob($data);

        return response($response);

    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function customerNotCall(Request $request)
    {
        // Validation
        $data = $request->all();

        $response = $this->repository->customerNotCall($data);

        return response($response);

    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function getPotentialJobs(Request $request)
    {
        /*
         * $data is never used, no need to waste time and memory on it.
         */
        // $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->repository->getPotentialJobs($user);

        return response($response);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function distanceFeed(Request $request)
    {
        $data = $request->all();

        /*
         * $data['distance'] != "" instead use single quotes like this:
         * $data['distance'] != ''
         * single quotes take less time to process.
         */
        if (isset($data['distance']) && $data['distance'] != '') {
            $distance = $data['distance'];
        } else {
            // here again, use single quotes
            $distance = '';
        }
        if (isset($data['time']) && $data['time'] != '') {
            $time = $data['time'];
        } else {
            $time = '';
        }
        if (isset($data['jobid']) && $data['jobid'] != '') {
            $jobid = $data['jobid'];
        }

        if (isset($data['session_time']) && $data['session_time'] != '') {
            $session = $data['session_time'];
        } else {
            $session = '';
        }

        if ($data['flagged'] == 'true') {
            /**
             * if($data['admincomment'] == '') {
             * instead checking it here and returning it back, use Validator with message.
             */
            if ($data['admincomment'] == '') {
                return 'Please, add comment';
            }
            $flagged = 'yes';
        } else {
            $flagged = 'no';
        }

        if ($data['manually_handled'] == 'true') {
            $manually_handled = 'yes';
        } else {
            $manually_handled = 'no';
        }

        if ($data['by_admin'] == 'true') {
            $by_admin = 'yes';
        } else {
            $by_admin = 'no';
        }

        if (isset($data['admincomment']) && $data['admincomment'] != '') {
            $admincomment = $data['admincomment'];
        } else {
            $admincomment = '';
        }
        if ($time || $distance) {
            /**
             * Queries should not be triggered from Controller directly, use Repositories, Model and Handlers instead.
             * also, no need to save number of affected rows, it doesn't have any special use here.
             *
             * $jobid can be null, and it should be $jobId.
             */

            Distance::where('job_id', '=', $jobid)->update(['distance' => $distance, 'time' => $time]);
        }

        if ($admincomment || $session || $flagged || $manually_handled || $by_admin) {

            Job::where('id', '=', $jobid)->update([
                'admin_comments' => $admincomment,
                'flagged' => $flagged,
                'session_time' => $session,
                'manually_handled' => $manually_handled,
                'by_admin' => $by_admin,
            ]);

        }

        return response('Record updated!');
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function reopen(Request $request)
    {
        //Validation
        $data = $request->all();
        $response = $this->repository->reopen($data);

        return response($response);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function resendNotifications(Request $request)
    {
        //Validation
        $data = $request->all();
        $job = $this->repository->find($data['jobid']);
        $job_data = $this->repository->jobToData($job);
        $this->repository->sendNotificationTranslator($job, $job_data, '*');

        return response(['success' => 'Push sent']);
    }

    /**
     * Sends SMS to Translator
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function resendSMSNotifications(Request $request)
    {
        $data = $request->all();
        $job = $this->repository->find($data['jobid']);
        //unused variable.
        $this->repository->jobToData($job);

        try {
            $this->repository->sendSMSNotificationToTranslator($job);
            return response(['success' => 'SMS sent']);
        } catch (\Exception $e) {
            return response(['success' => $e->getMessage()]);
        }
    }

}
