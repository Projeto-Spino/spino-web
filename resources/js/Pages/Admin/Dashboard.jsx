import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';

const tones = {
    warning: 'border-amber-200 bg-amber-50 text-amber-900',
    info: 'border-sky-200 bg-sky-50 text-sky-900',
    danger: 'border-rose-200 bg-rose-50 text-rose-900',
    success: 'border-emerald-200 bg-emerald-50 text-emerald-900',
};

export default function AdminDashboard({ metrics, currentTest, alerts }) {
    return <AdminLayout title="Visão geral" description="Acompanhe as etapas do evento e acesse rapidamente as tarefas que exigem atenção."><Head title="Administração" />
        <section className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Resumo do evento">
            <Metric label="Equipes ativas" value={`${metrics.activeTeams} / ${metrics.teamLimit}`} detail={`${metrics.pendingRegistrations} aguardando análise`} color="bg-sky-600" />
            <Metric label="Auditoria física" value={metrics.approvedAudits} detail={`${metrics.waitingAudits} aguardando conferência`} color="bg-emerald-600" />
            <Metric label="Juízes" value={metrics.judges} detail={`${metrics.completedVotes} votos concluídos`} color="bg-violet-600" />
            <Metric label="Janela de votação" value={metrics.votingOpen ? 'Aberta' : 'Fechada'} detail={metrics.votingOpen ? 'Recebendo avaliações' : 'Votos bloqueados'} color={metrics.votingOpen ? 'bg-emerald-600' : 'bg-slate-400'} />
        </section>

        <section className="mt-7 grid gap-6 xl:grid-cols-[1.25fr_.75fr]">
            <div className="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100 sm:p-6"><div className="flex items-center justify-between"><div><h2 className="text-xl font-bold">Pendências e alertas</h2><p className="mt-1 text-sm text-slate-500">Próximas ações recomendadas para a comissão.</p></div><span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">{alerts.length}</span></div><div className="mt-5 grid gap-3">{alerts.map((alert) => <article key={alert.title} className={`rounded-xl border p-4 ${tones[alert.tone]}`}><div className="flex flex-col justify-between gap-3 sm:flex-row sm:items-center"><div><h3 className="font-bold">{alert.title}</h3><p className="mt-1 text-sm opacity-80">{alert.description}</p></div><Link href={route(alert.route)} className="shrink-0 text-sm font-bold underline decoration-current/30 underline-offset-4">{alert.action} →</Link></div></article>)}{alerts.length === 0 && <div className="rounded-xl border border-dashed border-slate-300 p-8 text-center"><p className="font-bold text-slate-700">Nenhuma pendência crítica</p><p className="mt-1 text-sm text-slate-500">O fluxo do evento está em dia.</p></div>}</div></div>

            <div className="grid gap-6"><div className="rounded-2xl bg-[#172938] p-6 text-white shadow-sm"><p className="text-xs font-bold uppercase tracking-[.14em] text-orange-300">Ensaio atual</p>{currentTest ? <><h2 className="mt-3 text-2xl font-bold">{currentTest.name}</h2><p className="mt-2 text-sm text-slate-300">A ponte está marcada como ensaio em andamento.</p></> : <><h2 className="mt-3 text-xl font-bold">Nenhum ensaio em andamento</h2><p className="mt-2 text-sm text-slate-300">O placar público está aguardando a próxima ponte.</p></>}<a href={route('audience.dashboard')} target="_blank" rel="noreferrer" className="mt-5 inline-flex rounded-lg bg-white px-4 py-2 text-sm font-bold text-[#172938]">Abrir placar público ↗</a></div><QuickLinks /></div>
        </section>
    </AdminLayout>;
}

function Metric({ label, value, detail, color }) {
    return <article className="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100"><div className="flex items-center justify-between"><p className="text-sm font-semibold text-slate-500">{label}</p><span className={`size-3 rounded-full ${color}`} /></div><p className="mt-3 text-3xl font-bold tracking-tight text-slate-900">{value}</p><p className="mt-2 text-sm text-slate-500">{detail}</p></article>;
}

function QuickLinks() {
    const links = [['Inscrições', 'admin.registrations.index'], ['Organização', 'admin.bridge-audits.index'], ['Juízes e votação', 'admin.judges.index'], ['Relatórios e CSV', 'admin.reports.results.index']];
    return <div className="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100"><h2 className="font-bold">Acessos rápidos</h2><div className="mt-3 grid divide-y divide-slate-100">{links.map(([label, routeName]) => <Link key={routeName} href={route(routeName)} className="flex items-center justify-between py-3 text-sm font-semibold text-slate-700 hover:text-sky-700"><span>{label}</span><span>→</span></Link>)}</div></div>;
}
