import AdminLayout from '@dashboard/layouts/AdminLayout';
import DeleteUserForm from './Partials/DeleteUserForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';

const panel = 'rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm shadow-slate-900/[0.03] sm:p-8';

export default function Edit({ mustVerifyEmail, status }) {
    return (
        <AdminLayout title="Account" description="Your login details and password.">
            <div className="mx-auto max-w-3xl space-y-6">
                <div className={panel}>
                    <UpdateProfileInformationForm mustVerifyEmail={mustVerifyEmail} status={status} className="max-w-xl" />
                </div>

                <div className={panel}>
                    <UpdatePasswordForm className="max-w-xl" />
                </div>

                <div className={`${panel} border-red-200/70`}>
                    <DeleteUserForm className="max-w-xl" />
                </div>
            </div>
        </AdminLayout>
    );
}
