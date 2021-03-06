<?php

namespace App\Http\Controllers;


use App;
use App\Role;
use DateTime;
use DateTimeZone;
use DB;
use App\Http\Middleware\TrimStrings;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use App\User;
use App\Invite;
use Carbon\Carbon;
use App\Mail\InviteCreated;
use Illuminate\Http\Request;
use App\Http\Requests\InviteStoreRequest;
use App\Http\Requests\InviteEditRequest;
use App\Http\Requests\InvitePasswordRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;


use App\Exports\InviteExport;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class InviteController extends Controller
{

    /**
     * Examples
     *
     * Vue component example.
     *
     * <ui-select-pick-one
     *      label="My Label"
     *      url="/api-invite/options"
     *      class="form-group"
     *      v-model="inviteSelected"
     *      v-on:input="getData">
     * </ui-select-pick-one>
     *
     *
     * Blade component example.
     *
     *   In Controler
     *
     * $invite_options = \App\Invite::getOptions();
     *
     *   In View
     *
     * @component('../components/select-pick-one', [
     *      'fld' => 'invite_id',
     *      'selected_id' => $RECORD->invite_id,
     *      'first_option' => 'Select a Invites',
     *      'options' => $invite_options
     * ])
     * @endcomponent
     */


    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {

                info(__METHOD__);

        $x = Auth::user()->can('invite index');

        if (!Auth::user()->can('invite index')) {
            \Session::flash('flash_error_message', 'You do not have access to Users.');
            return Redirect::route('home');
        }

        // Remember the search parameters, we saved them in the Query
        $page = session('invite_page', '');
        $search = session('invite_keyword', '');
        $column = session('invite_column', 'Name');
        $direction = session('invite_direction', '-1');


        $can_add = Auth::user()->can('invite add');
        $can_show = Auth::user()->can('invite view');
        $can_edit = Auth::user()->can('invite edit');
        $can_delete = Auth::user()->can('invite delete');
        $can_excel = Auth::user()->can('invite export-excel');
        $can_pdf = Auth::user()->can('invite pdf');

        return view('invite.index', compact('page', 'column', 'direction', 'search', 'can_add', 'can_edit', 'can_delete', 'can_show', 'can_excel', 'can_pdf'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(Request $request)
    {

        if (!Auth::user()->can('invite add')) {  // TODO: add -> create
            \Session::flash('flash_error_message', 'You do not have access to add a User.');
            if (Auth::user()->can('invite index')) {
                return Redirect::route('invite.index');
            } else {
                return Redirect::route('home');
            }
        }

        $role_options = $this->getRoleOptions();
        $organization_options = App\Organization::getOptions(true);

        $organization_options = array_merge([
                -2 => ['id' => -2, 'name' => 'Assign Organizations for User'],
                -1 => ['id' => 0, 'name' => 'All Organizations']
            ]
            , $organization_options);
        $organization_id = -2;

        return view('invite.create', compact('role_options', 'organization_options', 'organization_id'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(InviteStoreRequest $request)
    {

        if (!Auth::user()->can('invite add')) {
            \Session::flash('flash_error_message', 'You do not have access to view a User.');
            if (Auth::user()->can('invite index')) {
                return Redirect::route('invite.index');
            } else {
                return Redirect::route('home');
            }
        }

        do {
            //generate a random string using Laravel's str_random helper
            $token = Str::random();
        } //check if the token already exists and if it does, try again
        while (Invite::where('token', $token)->first());

info(print_r($request->all(),true));
        //create a new invite record
        $invite = Invite::create([
            'organization_id' => $request->get('organization_id'),
            'email' => $request->get('email'),
            'name' => $request->get('name'),
            'role' => $request->get('role'),  //FIX  Roles on Invite screen are not valid roles #242
            'token' => $token,
            'expires_at' => $this->getExpiresAt()
        ]);

        // send the email
        Mail::to(lbv_set_to_email($request->get('email')))->queue(new InviteCreated($invite));

        \Session::flash('flash_success_message', 'Invited ' . $request->get('name') . ' ' . $request->get('email'));

        return Redirect::route('invite.index');

    }

    private function getExpiresAt()
    {
        return Carbon::now()->addDay(1);
    }

    public function resend(Request $request, $id)
    {

        if (!Auth::user()->can('invite add')) {
            \Session::flash('flash_error_message', 'You do not have access to view a User.');
            if (Auth::user()->can('invite index')) {
                return Redirect::route('invite.index');
            } else {
                return Redirect::route('home');
            }
        }

        $invite = $this->find($id);

        if ($invite) {
            $invite->update(['expires_at' => $this->getExpiresAt()]);
            // send the email
            Mail::to(lbv_set_to_email($invite->email))->queue(new InviteCreated($invite));
            \Session::flash('flash_success_message', 'Resent Invite for  ' . $invite->name . ' ' . $invite->email);

        } else {
            \Session::flash('flash_error_message', 'Unable to find Invite');
        }

        return Redirect::route('invite.index');

    }


    /**
     * This is where the link in the email ends up at.
     *
     * @param $token
     * @return Factory|RedirectResponse|View
     */
    public function accept($token)
    {
        // Look up the invite
        if (!$invite = Invite::select('token', 'expires_at', 'name', 'email', 'role')->where('token', $token)->first()) {
            //if the invite doesn't exist do something more graceful than this
            \Session::flash('flash_error_message', 'Unable to find your invitation.');
            return Redirect::route('login');
        }

        if ($invite->hasExpired()) {
            \Session::flash('flash_error_message', 'You invitation has expired.');
            return Redirect::route('login');
        }

        $user_inputs = explode(' ', $invite->name);
        $user_inputs[] = $invite->email;
        $user_inputs[] = $invite->role;

        return view('invite.create_password', compact('invite', 'user_inputs'));
    }

    public function createPassword(InvitePasswordRequest $request)
    {

        // Do we sill have an invite?
        if (!$invite = Invite::where('token', $request->token)->first()) {
            \Session::flash('flash_error_message', 'Unable to find your invitation.');
            return Redirect::route('login');
        }

        if ($invite->hasExpired()) {
            \Session::flash('flash_success_message', 'Your invitation has expired.');
            return Redirect::route('login');
        }

        DB::beginTransaction();
        // create the user with the details from the invite
        $user = User::create([
            'email' => $invite->email,
            'name' => $invite->name,
            'password' => bcrypt($request->password)
        ]);

        $user->assignRole($invite->role);

        // delete the invite so it can't be used again
        $invite->delete();

        DB::commit();

        // here you would probably log the user in and show them the dashboard, but we'll just prove it worked
        \Session::flash('flash_success_message', 'Please login with your email and password.');
        //return Redirect::route('login');
        return response()->json([
            'message' => 'Changed record'
        ], 200);
    }


    /**
     * Display the specified resource.
     *d
     * @param integer $id
     * @return Response
     */
    public function show($id)
    {

        if (!Auth::user()->can('invite view')) {
            \Session::flash('flash_error_message', 'You do not have access to view a User.');
            if (Auth::user()->can('invite index')) {
                return Redirect::route('invite.index');
            } else {
                return Redirect::route('home');
            }
        }

        if ($invite = $this->find($id)) {
            $can_edit = Auth::user()->can('invite edit');
            $can_delete = (Auth::user()->can('invite delete') && $invite->canDelete());
            return view('invite.show', compact('invite', 'user_inputs', 'can_edit', 'can_delete'));
        } else {
            \Session::flash('flash_error_message', 'Unable to find the invitation to display');
            return Redirect::route('invite.index');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param integer $id
     * @return Response
     */
    public function edit($id)
    {

        if (!Auth::user()->can('invite edit')) {
            \Session::flash('flash_error_message', 'You do not have access to edit a User.');
            if (Auth::user()->can('invite index')) {
                return Redirect::route('invite.index');
            } else {
                return Redirect::route('home');
            }
        }

        if ($invite = $this->find($id)) {
            $role_options = $this->getRoleOptions();
            $organization_options = App\Organization::getOptions(true);
            $organization_options = array_merge([
                    -2 => ['id' => -2, 'name' => 'Assign Organizations for User'],
                    -1 => ['id' => 0, 'name' => 'All Organizations']
                ]
                , $organization_options);
            return view('invite.edit', compact('invite', 'role_options', 'organization_options'));
        } else {
            \Session::flash('flash_error_message', 'Unable to find the invitation to edit');
            return Redirect::route('invite.index');
        }

    }

    private function getRoleOptions()
    {
        $role_options = Role::getOptions();

        return $role_options;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Invite $invite * @return \Illuminate\Http\Response
     */
    public function update(InviteEditRequest $request, $id)
    {

        if (!Auth::user()->can('invite edit')) {
            \Session::flash('flash_error_message', 'You do not have access to edit a User.');
            if (Auth::user()->can('invite index')) {
                return Redirect::route('invite.index');
            } else {
                return Redirect::route('home');
            }
        }
        if (!$invite = $this->find($id)) {
            \Session::flash('flash_error_message', 'Unable to find the invitation to update.');
            return Redirect::route('invite.index');
        }

        $invite->fill($request->all());

        if ($invite->isDirty()) {

            $invite->save();

            \Session::flash('flash_success_message', 'Invite ' . $invite->name . ' was changed');
        } else {
            \Session::flash('flash_info_message', 'No changes were made');
        }

        return Redirect::route('invite.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Invite $invite * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        if (!Auth::user()->can('invite delete')) {
            \Session::flash('flash_error_message', 'You do not have access to remove a User.');
            if (Auth::user()->can('invite index')) {
                return Redirect::route('invite.index');
            } else {
                return Redirect::route('home');
            }
        }

        $invite = $this->find($id);
        if ($invite) {
            $invite->delete();
            \Session::flash('flash_success_message', 'Invitation for ' . $invite->name . ' was removed.');
        } else {
            \Session::flash('flash_error_message', 'Unable to find Invite to delete');

        }

        return Redirect::route('invite.index');

    }

    /**
     * Find by ID, sanitize the ID first
     *
     * @param $id
     * @return Invite or null
     */
    private function find($id)
    {
        return Invite::find(intval($id));
    }


    public function download()
    {

        if (!Auth::user()->can('invite export-excel')) {
            \Session::flash('flash_error_message', 'You do not have access to download Users.');
            if (Auth::user()->can('invite index')) {
                return Redirect::route('invite.index');
            } else {
                return Redirect::route('home');
            }
        }

        // Remember the search parameters, we saved them in the Query
        $search = session('invite_keyword', '');
        $column = session('invite_column', 'name');
        $direction = session('invite_direction', '-1');

        $column = $column ? $column : 'name';

        // #TODO wrap in a try/catch and display english message on failuer.

        info(__METHOD__ . ' line: ' . __LINE__ . " $column, $direction, $search");

        $dataQuery = Invite::exportDataQuery($column, $direction, $search);
        //dump($data->toArray());
        //if ($data->count() > 0) {

        // TODO: is it possible to do 0 check before query executes somehow? i think the query would have to be executed twice, once for count, once for excel library
        return Excel::download(
            new InviteExport($dataQuery),
            'invite.xlsx'
        );

        //} else {
        //    \Session::flash('flash_error_message', 'There are no organizations to download.');
        //    return Redirect::route('organization.index');
        //}

    }

    public function print()
    {
        if (!Auth::user()->can('invite export-pdf')) { // TODO: i think these permissions may need to be updated to match initial permissions?
            \Session::flash('flash_error_message', 'You do not have access to print Users.');
            if (Auth::user()->can('invite index')) {
                return Redirect::route('invite.index');
            } else {
                return Redirect::route('home');
            }
        }

        if (!Auth::user()->can('invite export-pdf')) { // TODO: i think these permissions may need to be updated to match initial permissions?
            \Session::flash('flash_error_message', 'You do not have access to print Invite');
            if (Auth::user()->can('invite index')) {
                return Redirect::route('invite.index');
            } else {
                return Redirect::route('home');
            }
        }

        // Remember the search parameters, we saved them in the Query
        $search = session('invite_keyword', '');
        $column = session('invite_column', 'name');
        $direction = session('invite_direction', '-1');
        $column = $column ? $column : 'name';

        info(__METHOD__ . ' line: ' . __LINE__ . " $column, $direction, $search");

        // Get query data
        $columns = [
            'name',
            'email',
            'expires_at'
        ];
        $dataQuery = Invite::pdfDataQuery($column, $direction, $search, $columns);
        $data = $dataQuery->get();

        // Pass it to the view for html formatting:
        $printHtml = view('invite.print', compact('data'));

        // Begin DOMPDF/laravel-dompdf
        $pdf = App::make('dompdf.wrapper');
        $pdf->setPaper('a4', 'landscape');
        $pdf->setOptions(['isPhpEnabled' => TRUE]);
        $pdf->loadHTML($printHtml);
        $currentDate = new DateTime(null, new DateTimeZone('America/Chicago'));
        return $pdf->stream('invite-' . $currentDate->format('Ymd_Hi') . '.pdf');

        /*
        ///////////////////////////////////////////////////////////////////////
        /// Begin TCPDF/tcpdf-laravel test - keeping for reference
        // NOTE: you'll need to uncomment the use at the top for "PDF"
        PDF::SetTitle('Vendors');
        PDF::SetAutoPageBreak(TRUE, 15);
        PDF::SetMargins(PDF_MARGIN_LEFT, 15, PDF_MARGIN_RIGHT, 15);
        PDF::SetFooterMargin(PDF_MARGIN_FOOTER);
        PDF::setHeaderCallback(function($pdf){
            $currentDate = new \DateTime();
            $currentDate->setTimezone(new \DateTimeZone('America/Chicago'));
            $pdf->Cell(0,10,'Date ' . $currentDate->format('Y-m-d g:ia'),0,false,'C',0,'',0,false,'T','M');
        });
        PDF::setFooterCallback(function($pdf){
            //$pdf->SetY(-15);
            //var_dump(get_class_methods('Elibyy\TCPDF\TCPDFHelper')); exit;
            $pdf->Cell(0,10,'Page ' . $pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages(),0,false,'C',0,'',0,false,'T','M');
        });
        PDF::AddPage('L'); // Landscape
        //var_dump($dataQuery->get()); exit;
        //var_dump(get_class_methods('App\Exports\VcVendorExport')); exit; // query headings map download store queue toResponse
        PDF::writeHTML($html);
        PDF::Output('vc-vendor.pdf');
        /// End TCPDF/tcpdf-laravel test
        ///////////////////////////////////////////////////////////////////////
        */
    }

}
