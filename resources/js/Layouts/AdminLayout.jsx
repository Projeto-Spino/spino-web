import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';

const navigation = [
    { label: 'Visão geral', routeName: 'admin.dashboard', active: 'admin.dashboard', icon: 'dashboard' },
    { label: 'Inscrições', routeName: 'admin.registrations.index', active: 'admin.registrations.*', icon: 'teams' },
    { label: 'Organização', routeName: 'admin.bridge-audits.index', active: 'admin.bridge-audits.*', icon: 'audit' },
    { label: 'Ensaios', routeName: 'admin.test-runs.index', active: 'admin.test-runs.*', icon: 'test' },
    { label: 'Rupturas', routeName: 'admin.manual-rupture-results.index', active: 'admin.manual-rupture-results.*', icon: 'rupture' },
    { label: 'Juízes', routeName: 'admin.judges.index', active: 'admin.judges.*', icon: 'judges' },
    { label: 'Relatórios', routeName: 'admin.reports.results.index', active: 'admin.reports.*', icon: 'report' },
    { label: 'Equipes e pontes', routeName: 'admin.teams.index', active: 'admin.teams.*', icon: 'audit', requiresUserManagement: true },
    { label: 'Organizadores', routeName: 'admin.organizers.index', active: 'admin.organizers.*', icon: 'teams', requiresUserManagement: true },
];

export default function AdminLayout({ title, description, children, actions = null }) {
    const { auth } = usePage().props;
    const [menuOpen, setMenuOpen] = useState(false);

    return (
        <div className="min-h-screen bg-slate-50 text-slate-800">
            <aside className="fixed inset-y-0 left-0 z-40 hidden w-64 flex-col bg-[#172938] text-white lg:flex">
                <AdminBrand />
                <AdminNavigation onNavigate={() => setMenuOpen(false)} />
                <div className="mt-auto border-t border-white/10 p-4">
                    <ExternalLinks />
                    <UserSummary user={auth.user} />
                </div>
            </aside>

            {menuOpen && (
                <div className="fixed inset-0 z-50 lg:hidden">
                    <button type="button" aria-label="Fechar menu" className="absolute inset-0 bg-slate-950/55" onClick={() => setMenuOpen(false)} />
                    <aside className="relative flex h-full w-[min(88vw,320px)] flex-col overflow-hidden bg-[#172938] text-white shadow-2xl">
                        <div className="flex items-center justify-between pr-4"><AdminBrand /><button type="button" onClick={() => setMenuOpen(false)} className="grid size-10 place-items-center rounded-lg text-2xl text-slate-300 hover:bg-white/10">×</button></div>
                        <AdminNavigation onNavigate={() => setMenuOpen(false)} />
                        <div className="mt-auto border-t border-white/10 p-4"><ExternalLinks /><UserSummary user={auth.user} /></div>
                    </aside>
                </div>
            )}

            <div className="lg:pl-64">
                <header className="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur">
                    <div className="flex min-h-16 items-center justify-between gap-2 px-4 sm:gap-4 sm:px-8">
                        <div className="flex min-w-0 items-center gap-3">
                            <button type="button" aria-label="Abrir menu" onClick={() => setMenuOpen(true)} className="grid size-10 shrink-0 place-items-center rounded-lg border border-slate-200 text-xl lg:hidden">☰</button>
                            <div className="min-w-0"><p className="truncate text-sm font-semibold text-slate-500">Administração</p><p className="truncate font-bold text-slate-800">{title}</p></div>
                        </div>
                        <div className="flex items-center gap-1 sm:gap-3">
                            <span className="hidden text-sm font-semibold text-slate-600 sm:block">{auth.user.name}</span>
                            <span className="hidden size-9 place-items-center rounded-full bg-slate-200 font-bold text-[#172938] sm:grid">{auth.user.name.charAt(0).toUpperCase()}</span>
                            <Link href={route('logout')} method="post" as="button" className="rounded-lg px-2 py-2 text-sm font-semibold text-slate-500 hover:bg-slate-100 hover:text-slate-800 sm:px-3">Sair</Link>
                        </div>
                    </div>
                </header>

                <main className="mx-auto max-w-[1440px] px-4 py-6 sm:px-8 sm:py-9">
                    <div className="mb-7 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                        <div className="min-w-0"><p className="text-xs font-bold uppercase tracking-[.12em] text-sky-700 sm:tracking-[.14em]">IFSP · Organização do Concurso de Pontes</p><h1 className="mt-1 break-words text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{title}</h1>{description && <p className="mt-2 max-w-2xl text-sm text-slate-500 sm:text-base">{description}</p>}</div>
                        {actions && <div className="flex w-full shrink-0 flex-wrap gap-3 [&>*]:w-full sm:w-auto sm:[&>*]:w-auto">{actions}</div>}
                    </div>
                    {children}
                </main>
            </div>
        </div>
    );
}

function AdminBrand() {
    return <Link href={route('admin.dashboard')} className="flex h-20 items-center gap-3 px-6"><span className="grid size-10 place-items-center rounded-xl bg-emerald-700 font-black text-white">I</span><span><strong className="block text-lg">IFSP</strong><small className="text-slate-400">Comissão organizadora</small></span></Link>;
}

function AdminNavigation({ onNavigate }) {
    const { auth } = usePage().props;
    return <nav className="grid min-h-0 flex-1 content-start gap-1 overflow-y-auto px-3 py-5" aria-label="Navegação administrativa">{navigation.filter((item) => !item.requiresUserManagement || auth.canManageUsers).map((item) => {
        const active = route().current(item.active);
        return <Link key={item.routeName} href={route(item.routeName)} onClick={onNavigate} className={`flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition ${active ? 'bg-white text-[#172938] shadow' : 'text-slate-300 hover:bg-white/10 hover:text-white'}`}><AdminIcon name={item.icon} />{item.label}</Link>;
    })}</nav>;
}

function ExternalLinks() {
    return <div className="mb-4 grid gap-1"><a href={route('audience.dashboard')} target="_blank" rel="noreferrer" className="rounded-lg px-3 py-2 text-sm text-slate-300 hover:bg-white/10 hover:text-white">↗ Placar público</a><a href={route('registration.create')} target="_blank" rel="noreferrer" className="rounded-lg px-3 py-2 text-sm text-slate-300 hover:bg-white/10 hover:text-white">↗ Site institucional</a></div>;
}

function UserSummary({ user }) {
    return <div className="flex items-center gap-3 rounded-xl bg-white/5 p-3"><span className="grid size-9 place-items-center rounded-full bg-white/15 font-bold">{user.name.charAt(0).toUpperCase()}</span><span className="min-w-0"><strong className="block truncate text-sm">{user.name}</strong><small className="block truncate text-slate-400">{user.email}</small></span></div>;
}

function AdminIcon({ name }) {
    const paths = {
        dashboard: 'M4 4h6v6H4V4Zm10 0h6v9h-6V4ZM4 14h6v6H4v-6Zm10 3h6v3h-6v-3Z',
        teams: 'M16 11a4 4 0 1 0-3.2-6.4M8 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm0 2c-4 0-6 2-6 5v2h12v-2c0-3-2-5-6-5Zm8 0c-.7 0-1.4.1-2 .3 1.4 1.1 2 2.7 2 4.7v2h6v-2c0-3-2-5-6-5Z',
        audit: 'M9 3h6l1 2h3v16H5V5h3l1-2Zm-1 7h8M8 14h8M8 18h5',
        test: 'M5 3h14v4l-4 5v6l-3 3-3-3v-6L5 7V3Zm4 4h6',
        rupture: 'M12 3v10m0 0 4-4m-4 4-4-4M5 19h14M7 19v2m10-2v2',
        judges: 'M12 3a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm-7 17c0-4 2.5-7 7-7s7 3 7 7H5Zm14-10 2 2 3-4',
        report: 'M5 3h14v18H5V3Zm4 5h6M9 12h6M9 16h4',
    };
    return <svg viewBox="0 0 24 24" className="size-5 shrink-0" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round"><path d={paths[name]} /></svg>;
}
