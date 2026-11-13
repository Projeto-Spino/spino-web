import AdminLayout from '@/Layouts/AdminLayout';
import { Head } from '@inertiajs/react';

const sourceLabels = {
    sensor: 'Sensor',
    manual: 'Manual',
    pending: 'Sem resultado',
};

const sourceStyles = {
    sensor: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    manual: 'bg-amber-50 text-amber-700 ring-amber-200',
    pending: 'bg-slate-100 text-slate-600 ring-slate-200',
};

export default function ResultsReport({ rows, summary }) {
    const actions = <a href={route('admin.reports.results.export')} className="inline-flex min-h-11 items-center justify-center rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-800">↓ Exportar CSV</a>;

    return <AdminLayout title="Relatórios da competição" description="Consulte a classificação consolidada e exporte os resultados completos para CSV." actions={actions}>
        <Head title="Relatórios da competição" />

        <section className="grid gap-4 sm:grid-cols-3" aria-label="Resumo dos resultados">
            <SummaryCard label="Equipes classificadas" value={summary.eligibleTeams} detail="Aprovadas na auditoria" />
            <SummaryCard label="Ensaios concluídos" value={summary.completedTests} detail="Com carga de ruptura" />
            <SummaryCard label="Votos computados" value={summary.completedJudges} detail="Avaliações estéticas" />
        </section>

        <section className="mt-7 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-100 sm:p-6">
            <div className="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div><h2 className="text-xl font-bold text-slate-900">Classificação consolidada</h2><p className="mt-1 text-sm text-slate-500">A ordem segue a mesma regra exibida no placar público.</p></div>
                <p className="text-xs font-semibold text-slate-500">O CSV inclui integrantes, cursos, cargas, datas e origem do resultado.</p>
            </div>

            <div className="admin-table-wrap mt-6 overflow-x-auto">
                <table className="admin-responsive-table w-full min-w-[920px] text-left text-sm">
                    <thead className="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500"><tr><th className="rounded-l-md px-4 py-3">Pos.</th><th className="px-4 py-3">Equipe</th><th className="px-4 py-3">Integrantes</th><th className="px-4 py-3">Ruptura</th><th className="px-4 py-3">Eficiência</th><th className="px-4 py-3">Precisão</th><th className="px-4 py-3">Estética</th><th className="rounded-r-md px-4 py-3">Total</th></tr></thead>
                    <tbody className="divide-y divide-slate-200">{rows.map((row) => <tr key={row.code} className="hover:bg-slate-50/70"><td data-label="Posição" className="px-4 py-4 text-lg font-black text-sky-700">{row.position}º</td><td data-label="Equipe" className="px-4 py-4"><strong className="block text-slate-900">{row.name}</strong><small className="font-mono text-xs text-slate-500">{row.code}</small></td><td data-label="Integrantes" className="px-4 py-4 font-semibold">{row.memberCount}</td><td data-label="Ruptura" className="px-4 py-4"><strong className="block">{formatKg(row.ruptureLoadKg)}</strong><span className={`mt-1 inline-flex rounded-full px-2 py-0.5 text-[11px] font-bold ring-1 ${sourceStyles[row.ruptureSource]}`}>{sourceLabels[row.ruptureSource]}</span></td><ScoreCell label="Eficiência" value={row.efficiency} /><ScoreCell label="Precisão" value={row.precision} /><ScoreCell label="Estética" value={row.aesthetic} /><td data-label="Total" className="px-4 py-4 text-lg font-black text-emerald-700">{formatScore(row.total)}</td></tr>)}</tbody>
                </table>
                {!rows.length && <div className="py-14 text-center"><p className="font-bold text-slate-700">Nenhum resultado disponível</p><p className="mt-1 text-sm text-slate-500">As equipes aparecem aqui após a aprovação na auditoria.</p></div>}
            </div>
        </section>
    </AdminLayout>;
}

function SummaryCard({ label, value, detail }) {
    return <article className="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100"><p className="text-sm font-semibold text-slate-500">{label}</p><p className="mt-2 text-3xl font-black text-slate-900">{value}</p><p className="mt-1 text-sm text-slate-500">{detail}</p></article>;
}

function ScoreCell({ label, value }) {
    return <td data-label={label} className="px-4 py-4 font-bold text-slate-700">{formatScore(value)}</td>;
}

function formatScore(value) {
    return Number(value ?? 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatKg(value) {
    return value === null ? '—' : `${Number(value).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} kg`;
}
