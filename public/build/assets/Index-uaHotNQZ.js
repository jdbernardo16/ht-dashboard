import {
    m as c,
    j as _,
    y as re,
    x as le,
    d as ae,
    o as n,
    w as g,
    e,
    a as C,
    c as i,
    t as l,
    i as U,
    k as m,
    p as T,
    F as x,
    z as y,
    q as M,
    g as k,
    n as S,
    s as f,
} from "./app-NpyZKQPK.js";
import { _ as ne } from "./AuthenticatedLayout-DcS0JwOY.js";
import { D as ie, _ as de } from "./Pagination-DqAuicJN.js";
import { _ as ue } from "./FormModal-CgEwW3r6.js";
import "./_plugin-vue_export-helper-DlAUqK2U.js";
import "./Modal-CXplEik2.js";
const ge = { class: "max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" },
    ce = { class: "grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8" },
    me = {
        class: "bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-3 border border-blue-100",
    },
    fe = { class: "flex items-center" },
    pe = { class: "ml-4" },
    be = { class: "text-2xl font-bold text-gray-900" },
    ve = {
        class: "bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-3 border border-green-100",
    },
    xe = { class: "flex items-center" },
    ye = { class: "ml-4" },
    ke = { class: "text-2xl font-bold text-gray-900" },
    we = {
        class: "bg-gradient-to-r from-yellow-50 to-orange-50 rounded-xl p-3 border border-yellow-100",
    },
    he = { class: "flex items-center" },
    _e = { class: "ml-4" },
    Ce = { class: "text-2xl font-bold text-gray-900" },
    Te = {
        class: "bg-gradient-to-r from-red-50 to-pink-50 rounded-xl p-3 border border-red-100",
    },
    Me = { class: "flex items-center" },
    Se = { class: "ml-4" },
    je = { class: "text-2xl font-bold text-gray-900" },
    De = {
        class: "bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8",
    },
    Fe = { class: "p-6" },
    Ae = { class: "grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4" },
    Ne = { class: "lg:col-span-2" },
    Be = { class: "relative" },
    Pe = ["value"],
    Ue = ["value"],
    Ve = { class: "mt-4" },
    Ee = ["aria-expanded"],
    qe = {
        key: 0,
        class: "mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4",
    },
    Oe = ["value"],
    ze = { class: "mt-6 flex flex-col sm:flex-row gap-3 justify-end" },
    He = ["disabled"],
    Ie = {
        key: 0,
        class: "bg-white rounded-xl shadow-sm border border-gray-200 p-6",
    },
    $e = { class: "animate-pulse space-y-4" },
    Le = { class: "space-y-3" },
    Ye = {
        key: 1,
        class: "bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center",
    },
    Ge = {
        key: 2,
        class: "bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center",
    },
    Je = {
        key: 3,
        class: "bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden",
    },
    Ke = { class: "relative" },
    Qe = {
        key: 0,
        class: "absolute inset-0 bg-white bg-opacity-50 z-10 flex items-center justify-center",
    },
    Re = { class: "font-medium text-gray-900" },
    We = { class: "border-t border-gray-200" },
    lt = {
        __name: "Index",
        props: {
            tasks: { type: Object, required: !0 },
            users: { type: Array, default: () => [] },
            goals: { type: Array, default: () => [] },
            filters: { type: Object, default: () => ({}) },
        },
        setup(V) {
            const a = V,
                d = c(!1),
                j = c(!1),
                p = c(!1),
                E = c(!1),
                D = c({}),
                r = c({
                    search: a.filters.search || "",
                    status: a.filters.status || "",
                    priority: a.filters.priority || "",
                    assigned_to: a.filters.assigned_to || "",
                    date_from: a.filters.date_from || "",
                    date_to: a.filters.date_to || "",
                }),
                F = [
                    { value: "pending", label: "Pending" },
                    { value: "not_started", label: "Not Started" },
                    { value: "in_progress", label: "In Progress" },
                    { value: "completed", label: "Completed" },
                    { value: "cancelled", label: "Cancelled" },
                ],
                A = [
                    { value: "low", label: "Low" },
                    { value: "medium", label: "Medium" },
                    { value: "high", label: "High" },
                    { value: "urgent", label: "Urgent" },
                ],
                q = [
                    { key: "id", label: "ID", sortable: !0 },
                    { key: "title", label: "Title", sortable: !0 },
                    { key: "priority", label: "Priority", sortable: !0 },
                    { key: "status", label: "Status", sortable: !0 },
                    {
                        key: "due_date",
                        label: "Due Date",
                        type: "date",
                        sortable: !0,
                    },
                    { key: "assigned_to", label: "Assigned To", sortable: !0 },
                    {
                        key: "estimated_hours",
                        label: "Est. Hours",
                        sortable: !0,
                    },
                ],
                O = [
                    { value: "pending", label: "Pending" },
                    { value: "not_started", label: "Not Started" },
                    { value: "in_progress", label: "In Progress" },
                    { value: "completed", label: "Completed" },
                    { value: "cancelled", label: "Cancelled" },
                ],
                u = _(() => a.tasks.data || []),
                w = _(() => Object.values(r.value).some((o) => o !== "")),
                N = _(() =>
                    a.users.map((o) => ({
                        value: o.id,
                        label: o.name || o.full_name,
                    }))
                ),
                z = [
                    {
                        name: "title",
                        label: "Title",
                        type: "text",
                        required: !0,
                    },
                    {
                        name: "description",
                        label: "Description",
                        type: "textarea",
                        required: !1,
                        rows: 4,
                    },
                    {
                        name: "status",
                        label: "Status",
                        type: "select",
                        required: !0,
                        options: F,
                    },
                    {
                        name: "priority",
                        label: "Priority",
                        type: "select",
                        required: !0,
                        options: A,
                    },
                    {
                        name: "assigned_to",
                        label: "Assigned To",
                        type: "select",
                        required: !1,
                        options: N,
                    },
                    {
                        name: "due_date",
                        label: "Due Date",
                        type: "date",
                        required: !1,
                    },
                    {
                        name: "estimated_hours",
                        label: "Estimated Hours",
                        type: "number",
                        required: !1,
                        min: 0,
                        step: 0.5,
                    },
                    {
                        name: "notes",
                        label: "Notes",
                        type: "textarea",
                        required: !1,
                        rows: 3,
                    },
                ],
                h = () => {
                    f.visit("/tasks/create");
                },
                H = (o) => {
                    f.visit(`/tasks/${o.id}/edit`);
                },
                I = (o) => {
                    f.visit(`/tasks/${o.id}`);
                },
                $ = async (o) => {
                    if (confirm("Are you sure you want to delete this task?"))
                        try {
                            (d.value = !0),
                                await f.delete(`/tasks/${o.id}`, {
                                    preserveScroll: !0,
                                    onSuccess: () => {},
                                    onError: (t) => {
                                        console.error(
                                            "Error deleting task:",
                                            t
                                        ),
                                            t.message?.includes("403") &&
                                                alert(
                                                    "You don't have permission to delete this task."
                                                );
                                    },
                                    onFinish: () => {
                                        d.value = !1;
                                    },
                                });
                        } catch (t) {
                            (d.value = !1),
                                console.error("Error deleting task:", t);
                        }
                },
                L = () => {
                    clearTimeout(window.searchTimeout),
                        (window.searchTimeout = setTimeout(() => {
                            v();
                        }, 300));
                },
                b = () => {
                    v();
                },
                v = () => {
                    d.value = !0;
                    const o = {};
                    Object.keys(r.value).forEach((t) => {
                        r.value[t] && (o[t] = r.value[t]);
                    }),
                        f.get("/tasks", o, {
                            preserveState: !0,
                            preserveScroll: !0,
                            only: ["tasks", "filters"],
                            onError: (t) => {
                                console.error("Error applying filters:", t),
                                    t.message?.includes("403") &&
                                        alert(
                                            "You don't have permission to view tasks."
                                        );
                            },
                            onFinish: () => {
                                d.value = !1;
                            },
                        });
                },
                B = () => {
                    (r.value = {
                        search: "",
                        status: "",
                        priority: "",
                        assigned_to: "",
                        date_from: "",
                        date_to: "",
                    }),
                        v();
                },
                Y = (o) => {
                    f.visit(o, {
                        preserveState: !0,
                        preserveScroll: !0,
                        only: ["tasks", "users", "goals", "filters"],
                    });
                },
                G = () => {
                    (j.value = !1),
                        (D.value = {}),
                        formModal.value && formModal.value.resetForm();
                },
                J = (o) => {
                    d.value = !0;
                },
                K = () =>
                    u.value.filter((o) => o.status === "completed").length,
                Q = () =>
                    u.value.filter((o) => o.status === "in_progress").length,
                R = () => {
                    const o = new Date();
                    return u.value.filter(
                        (t) =>
                            t.due_date &&
                            new Date(t.due_date) < o &&
                            t.status !== "completed"
                    ).length;
                },
                W = (o) => (o ? new Date(o).toLocaleDateString() : "N/A"),
                X = (o) =>
                    o
                        ? o.charAt(0).toUpperCase() +
                          o.slice(1).replace("_", " ")
                        : "Unknown",
                Z = (o) =>
                    o ? o.charAt(0).toUpperCase() + o.slice(1) : "Unknown",
                ee = (o) =>
                    ({
                        pending: "bg-yellow-100 text-yellow-800",
                        not_started: "bg-gray-100 text-gray-800",
                        in_progress: "bg-blue-100 text-blue-800",
                        completed: "bg-green-100 text-green-800",
                        cancelled: "bg-red-100 text-red-800",
                    }[o] || "bg-gray-100 text-gray-800"),
                te = (o) =>
                    ({
                        low: "bg-gray-100 text-gray-800",
                        medium: "bg-yellow-100 text-yellow-800",
                        high: "bg-orange-100 text-orange-800",
                        urgent: "bg-red-100 text-red-800",
                    }[o] || "bg-gray-100 text-gray-800"),
                se = (o, t) => {
                    if (!o || t === "completed") return "text-gray-900";
                    const s = new Date(),
                        oe = new Date(o) - s,
                        P = Math.ceil(oe / (1e3 * 60 * 60 * 24));
                    return P < 0
                        ? "text-red-600 font-medium"
                        : P <= 3
                        ? "text-yellow-600 font-medium"
                        : "text-gray-900";
                };
            return (
                re(
                    r,
                    () => {
                        clearTimeout(window.filterTimeout),
                            (window.filterTimeout = setTimeout(() => {
                                v();
                            }, 300));
                    },
                    { deep: !0 }
                ),
                le(() => {}),
                (o, t) => (
                    n(),
                    ae(ne, null, {
                        header: g(() => [
                            e(
                                "div",
                                {
                                    class: "flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4",
                                },
                                [
                                    t[8] ||
                                        (t[8] = e(
                                            "div",
                                            null,
                                            [
                                                e(
                                                    "h1",
                                                    {
                                                        class: "text-2xl font-bold text-gray-900 leading-tight",
                                                    },
                                                    " Task Management "
                                                ),
                                                e(
                                                    "p",
                                                    {
                                                        class: "mt-1 text-sm text-gray-500",
                                                    },
                                                    " Manage and track all your tasks in one place "
                                                ),
                                            ],
                                            -1
                                        )),
                                    e(
                                        "div",
                                        {
                                            class: "flex flex-col sm:flex-row sm:items-center sm:space-x-3 space-y-2 sm:space-y-0 mt-4 lg:mt-0",
                                        },
                                        [
                                            e(
                                                "button",
                                                {
                                                    onClick: h,
                                                    class: "inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-200 shadow-sm",
                                                },
                                                t[7] ||
                                                    (t[7] = [
                                                        e(
                                                            "svg",
                                                            {
                                                                class: "w-4 h-4 mr-2",
                                                                fill: "none",
                                                                stroke: "currentColor",
                                                                viewBox:
                                                                    "0 0 24 24",
                                                            },
                                                            [
                                                                e("path", {
                                                                    "stroke-linecap":
                                                                        "round",
                                                                    "stroke-linejoin":
                                                                        "round",
                                                                    "stroke-width":
                                                                        "2",
                                                                    d: "M12 4v16m8-8H4",
                                                                }),
                                                            ],
                                                            -1
                                                        ),
                                                        k(" New Task ", -1),
                                                    ])
                                            ),
                                        ]
                                    ),
                                ]
                            ),
                        ]),
                        default: g(() => [
                            e("div", ge, [
                                e("div", ce, [
                                    e("div", me, [
                                        e("div", fe, [
                                            t[10] ||
                                                (t[10] = e(
                                                    "div",
                                                    { class: "flex-shrink-0" },
                                                    [
                                                        e(
                                                            "div",
                                                            {
                                                                class: "inline-flex items-center justify-center w-12 h-12 bg-blue-100 rounded-full",
                                                            },
                                                            [
                                                                e(
                                                                    "svg",
                                                                    {
                                                                        class: "w-6 h-6 text-blue-600",
                                                                        fill: "none",
                                                                        stroke: "currentColor",
                                                                        viewBox:
                                                                            "0 0 24 24",
                                                                    },
                                                                    [
                                                                        e(
                                                                            "path",
                                                                            {
                                                                                "stroke-linecap":
                                                                                    "round",
                                                                                "stroke-linejoin":
                                                                                    "round",
                                                                                "stroke-width":
                                                                                    "2",
                                                                                d: "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2",
                                                                            }
                                                                        ),
                                                                    ]
                                                                ),
                                                            ]
                                                        ),
                                                    ],
                                                    -1
                                                )),
                                            e("div", pe, [
                                                t[9] ||
                                                    (t[9] = e(
                                                        "p",
                                                        {
                                                            class: "text-sm font-medium text-blue-600",
                                                        },
                                                        " Total Tasks ",
                                                        -1
                                                    )),
                                                e(
                                                    "p",
                                                    be,
                                                    l(u.value.length),
                                                    1
                                                ),
                                            ]),
                                        ]),
                                    ]),
                                    e("div", ve, [
                                        e("div", xe, [
                                            t[12] ||
                                                (t[12] = e(
                                                    "div",
                                                    { class: "flex-shrink-0" },
                                                    [
                                                        e(
                                                            "div",
                                                            {
                                                                class: "inline-flex items-center justify-center w-12 h-12 bg-green-100 rounded-full",
                                                            },
                                                            [
                                                                e(
                                                                    "svg",
                                                                    {
                                                                        class: "w-6 h-6 text-green-600",
                                                                        fill: "none",
                                                                        stroke: "currentColor",
                                                                        viewBox:
                                                                            "0 0 24 24",
                                                                    },
                                                                    [
                                                                        e(
                                                                            "path",
                                                                            {
                                                                                "stroke-linecap":
                                                                                    "round",
                                                                                "stroke-linejoin":
                                                                                    "round",
                                                                                "stroke-width":
                                                                                    "2",
                                                                                d: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z",
                                                                            }
                                                                        ),
                                                                    ]
                                                                ),
                                                            ]
                                                        ),
                                                    ],
                                                    -1
                                                )),
                                            e("div", ye, [
                                                t[11] ||
                                                    (t[11] = e(
                                                        "p",
                                                        {
                                                            class: "text-sm font-medium text-green-600",
                                                        },
                                                        " Completed ",
                                                        -1
                                                    )),
                                                e("p", ke, l(K()), 1),
                                            ]),
                                        ]),
                                    ]),
                                    e("div", we, [
                                        e("div", he, [
                                            t[14] ||
                                                (t[14] = e(
                                                    "div",
                                                    { class: "flex-shrink-0" },
                                                    [
                                                        e(
                                                            "div",
                                                            {
                                                                class: "inline-flex items-center justify-center w-12 h-12 bg-yellow-100 rounded-full",
                                                            },
                                                            [
                                                                e(
                                                                    "svg",
                                                                    {
                                                                        class: "w-6 h-6 text-yellow-600",
                                                                        fill: "none",
                                                                        stroke: "currentColor",
                                                                        viewBox:
                                                                            "0 0 24 24",
                                                                    },
                                                                    [
                                                                        e(
                                                                            "path",
                                                                            {
                                                                                "stroke-linecap":
                                                                                    "round",
                                                                                "stroke-linejoin":
                                                                                    "round",
                                                                                "stroke-width":
                                                                                    "2",
                                                                                d: "M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z",
                                                                            }
                                                                        ),
                                                                    ]
                                                                ),
                                                            ]
                                                        ),
                                                    ],
                                                    -1
                                                )),
                                            e("div", _e, [
                                                t[13] ||
                                                    (t[13] = e(
                                                        "p",
                                                        {
                                                            class: "text-sm font-medium text-yellow-600",
                                                        },
                                                        " In Progress ",
                                                        -1
                                                    )),
                                                e("p", Ce, l(Q()), 1),
                                            ]),
                                        ]),
                                    ]),
                                    e("div", Te, [
                                        e("div", Me, [
                                            t[16] ||
                                                (t[16] = e(
                                                    "div",
                                                    { class: "flex-shrink-0" },
                                                    [
                                                        e(
                                                            "div",
                                                            {
                                                                class: "inline-flex items-center justify-center w-12 h-12 bg-red-100 rounded-full",
                                                            },
                                                            [
                                                                e(
                                                                    "svg",
                                                                    {
                                                                        class: "w-6 h-6 text-red-600",
                                                                        fill: "none",
                                                                        stroke: "currentColor",
                                                                        viewBox:
                                                                            "0 0 24 24",
                                                                    },
                                                                    [
                                                                        e(
                                                                            "path",
                                                                            {
                                                                                "stroke-linecap":
                                                                                    "round",
                                                                                "stroke-linejoin":
                                                                                    "round",
                                                                                "stroke-width":
                                                                                    "2",
                                                                                d: "M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z",
                                                                            }
                                                                        ),
                                                                    ]
                                                                ),
                                                            ]
                                                        ),
                                                    ],
                                                    -1
                                                )),
                                            e("div", Se, [
                                                t[15] ||
                                                    (t[15] = e(
                                                        "p",
                                                        {
                                                            class: "text-sm font-medium text-red-600",
                                                        },
                                                        " Overdue ",
                                                        -1
                                                    )),
                                                e("p", je, l(R()), 1),
                                            ]),
                                        ]),
                                    ]),
                                ]),
                                e("div", De, [
                                    t[28] ||
                                        (t[28] = e(
                                            "div",
                                            {
                                                class: "bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4",
                                            },
                                            [
                                                e(
                                                    "h2",
                                                    {
                                                        class: "text-xl font-semibold text-white",
                                                    },
                                                    " Search & Filter "
                                                ),
                                            ],
                                            -1
                                        )),
                                    e("div", Fe, [
                                        e("div", Ae, [
                                            e("div", Ne, [
                                                t[18] ||
                                                    (t[18] = e(
                                                        "label",
                                                        {
                                                            for: "search",
                                                            class: "block text-sm font-medium text-gray-700 mb-1",
                                                        },
                                                        " Search tasks ",
                                                        -1
                                                    )),
                                                e("div", Be, [
                                                    t[17] ||
                                                        (t[17] = e(
                                                            "div",
                                                            {
                                                                class: "absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none",
                                                            },
                                                            [
                                                                e(
                                                                    "svg",
                                                                    {
                                                                        class: "h-5 w-5 text-gray-400",
                                                                        fill: "none",
                                                                        stroke: "currentColor",
                                                                        viewBox:
                                                                            "0 0 24 24",
                                                                    },
                                                                    [
                                                                        e(
                                                                            "path",
                                                                            {
                                                                                "stroke-linecap":
                                                                                    "round",
                                                                                "stroke-linejoin":
                                                                                    "round",
                                                                                "stroke-width":
                                                                                    "2",
                                                                                d: "M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z",
                                                                            }
                                                                        ),
                                                                    ]
                                                                ),
                                                            ],
                                                            -1
                                                        )),
                                                    m(
                                                        e(
                                                            "input",
                                                            {
                                                                id: "search",
                                                                "onUpdate:modelValue":
                                                                    t[0] ||
                                                                    (t[0] = (
                                                                        s
                                                                    ) =>
                                                                        (r.value.search =
                                                                            s)),
                                                                type: "text",
                                                                placeholder:
                                                                    "Search tasks...",
                                                                class: "block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-colors duration-200",
                                                                onInput: L,
                                                                "aria-describedby":
                                                                    "search-description",
                                                            },
                                                            null,
                                                            544
                                                        ),
                                                        [[T, r.value.search]]
                                                    ),
                                                ]),
                                                t[19] ||
                                                    (t[19] = e(
                                                        "p",
                                                        {
                                                            id: "search-description",
                                                            class: "mt-1 text-xs text-gray-500",
                                                        },
                                                        " Search by title, description, or assignee ",
                                                        -1
                                                    )),
                                            ]),
                                            e("div", null, [
                                                t[21] ||
                                                    (t[21] = e(
                                                        "label",
                                                        {
                                                            for: "status",
                                                            class: "block text-sm font-medium text-gray-700 mb-1",
                                                        },
                                                        " Status ",
                                                        -1
                                                    )),
                                                m(
                                                    e(
                                                        "select",
                                                        {
                                                            id: "status",
                                                            "onUpdate:modelValue":
                                                                t[1] ||
                                                                (t[1] = (s) =>
                                                                    (r.value.status =
                                                                        s)),
                                                            class: "block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200",
                                                            onChange: b,
                                                        },
                                                        [
                                                            t[20] ||
                                                                (t[20] = e(
                                                                    "option",
                                                                    {
                                                                        value: "",
                                                                    },
                                                                    "All Status",
                                                                    -1
                                                                )),
                                                            (n(),
                                                            i(
                                                                x,
                                                                null,
                                                                y(F, (s) =>
                                                                    e(
                                                                        "option",
                                                                        {
                                                                            key: s.value,
                                                                            value: s.value,
                                                                        },
                                                                        l(
                                                                            s.label
                                                                        ),
                                                                        9,
                                                                        Pe
                                                                    )
                                                                ),
                                                                64
                                                            )),
                                                        ],
                                                        544
                                                    ),
                                                    [[M, r.value.status]]
                                                ),
                                            ]),
                                            e("div", null, [
                                                t[23] ||
                                                    (t[23] = e(
                                                        "label",
                                                        {
                                                            for: "priority",
                                                            class: "block text-sm font-medium text-gray-700 mb-1",
                                                        },
                                                        " Priority ",
                                                        -1
                                                    )),
                                                m(
                                                    e(
                                                        "select",
                                                        {
                                                            id: "priority",
                                                            "onUpdate:modelValue":
                                                                t[2] ||
                                                                (t[2] = (s) =>
                                                                    (r.value.priority =
                                                                        s)),
                                                            class: "block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200",
                                                            onChange: b,
                                                        },
                                                        [
                                                            t[22] ||
                                                                (t[22] = e(
                                                                    "option",
                                                                    {
                                                                        value: "",
                                                                    },
                                                                    "All Priorities",
                                                                    -1
                                                                )),
                                                            (n(),
                                                            i(
                                                                x,
                                                                null,
                                                                y(A, (s) =>
                                                                    e(
                                                                        "option",
                                                                        {
                                                                            key: s.value,
                                                                            value: s.value,
                                                                        },
                                                                        l(
                                                                            s.label
                                                                        ),
                                                                        9,
                                                                        Ue
                                                                    )
                                                                ),
                                                                64
                                                            )),
                                                        ],
                                                        544
                                                    ),
                                                    [[M, r.value.priority]]
                                                ),
                                            ]),
                                        ]),
                                        e("div", Ve, [
                                            e(
                                                "button",
                                                {
                                                    onClick:
                                                        t[3] ||
                                                        (t[3] = (s) =>
                                                            (p.value =
                                                                !p.value)),
                                                    class: "text-sm text-indigo-600 hover:text-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors duration-200",
                                                    "aria-expanded": p.value,
                                                },
                                                l(p.value ? "Hide" : "Show") +
                                                    " advanced filters ",
                                                9,
                                                Ee
                                            ),
                                        ]),
                                        p.value
                                            ? (n(),
                                              i("div", qe, [
                                                  e("div", null, [
                                                      t[25] ||
                                                          (t[25] = e(
                                                              "label",
                                                              {
                                                                  for: "assigned_to",
                                                                  class: "block text-sm font-medium text-gray-700 mb-1",
                                                              },
                                                              " Assigned To ",
                                                              -1
                                                          )),
                                                      m(
                                                          e(
                                                              "select",
                                                              {
                                                                  id: "assigned_to",
                                                                  "onUpdate:modelValue":
                                                                      t[4] ||
                                                                      (t[4] = (
                                                                          s
                                                                      ) =>
                                                                          (r.value.assigned_to =
                                                                              s)),
                                                                  class: "block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200",
                                                                  onChange: b,
                                                              },
                                                              [
                                                                  t[24] ||
                                                                      (t[24] =
                                                                          e(
                                                                              "option",
                                                                              {
                                                                                  value: "",
                                                                              },
                                                                              "All Users",
                                                                              -1
                                                                          )),
                                                                  (n(!0),
                                                                  i(
                                                                      x,
                                                                      null,
                                                                      y(
                                                                          N.value,
                                                                          (
                                                                              s
                                                                          ) => (
                                                                              n(),
                                                                              i(
                                                                                  "option",
                                                                                  {
                                                                                      key: s.value,
                                                                                      value: s.value,
                                                                                  },
                                                                                  l(
                                                                                      s.label
                                                                                  ),
                                                                                  9,
                                                                                  Oe
                                                                              )
                                                                          )
                                                                      ),
                                                                      128
                                                                  )),
                                                              ],
                                                              544
                                                          ),
                                                          [
                                                              [
                                                                  M,
                                                                  r.value
                                                                      .assigned_to,
                                                              ],
                                                          ]
                                                      ),
                                                  ]),
                                                  e("div", null, [
                                                      t[26] ||
                                                          (t[26] = e(
                                                              "label",
                                                              {
                                                                  for: "date_from",
                                                                  class: "block text-sm font-medium text-gray-700 mb-1",
                                                              },
                                                              " Date From ",
                                                              -1
                                                          )),
                                                      m(
                                                          e(
                                                              "input",
                                                              {
                                                                  id: "date_from",
                                                                  "onUpdate:modelValue":
                                                                      t[5] ||
                                                                      (t[5] = (
                                                                          s
                                                                      ) =>
                                                                          (r.value.date_from =
                                                                              s)),
                                                                  type: "date",
                                                                  class: "block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200",
                                                                  onChange: b,
                                                              },
                                                              null,
                                                              544
                                                          ),
                                                          [
                                                              [
                                                                  T,
                                                                  r.value
                                                                      .date_from,
                                                              ],
                                                          ]
                                                      ),
                                                  ]),
                                                  e("div", null, [
                                                      t[27] ||
                                                          (t[27] = e(
                                                              "label",
                                                              {
                                                                  for: "date_to",
                                                                  class: "block text-sm font-medium text-gray-700 mb-1",
                                                              },
                                                              " Date To ",
                                                              -1
                                                          )),
                                                      m(
                                                          e(
                                                              "input",
                                                              {
                                                                  id: "date_to",
                                                                  "onUpdate:modelValue":
                                                                      t[6] ||
                                                                      (t[6] = (
                                                                          s
                                                                      ) =>
                                                                          (r.value.date_to =
                                                                              s)),
                                                                  type: "date",
                                                                  class: "block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200",
                                                                  onChange: b,
                                                              },
                                                              null,
                                                              544
                                                          ),
                                                          [[T, r.value.date_to]]
                                                      ),
                                                  ]),
                                              ]))
                                            : U("", !0),
                                        e("div", ze, [
                                            e(
                                                "button",
                                                {
                                                    onClick: B,
                                                    disabled: !w.value,
                                                    class: "px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200",
                                                    "aria-label":
                                                        "Clear all filters",
                                                },
                                                " Clear Filters ",
                                                8,
                                                He
                                            ),
                                            e(
                                                "button",
                                                {
                                                    onClick: v,
                                                    class: "px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 shadow-sm",
                                                    "aria-label":
                                                        "Apply filters",
                                                },
                                                " Apply Filters "
                                            ),
                                        ]),
                                    ]),
                                ]),
                                d.value && u.value.length === 0
                                    ? (n(),
                                      i("div", Ie, [
                                          e("div", $e, [
                                              t[29] ||
                                                  (t[29] = e(
                                                      "div",
                                                      {
                                                          class: "h-4 bg-gray-200 rounded w-1/4",
                                                      },
                                                      null,
                                                      -1
                                                  )),
                                              e("div", Le, [
                                                  (n(),
                                                  i(
                                                      x,
                                                      null,
                                                      y(5, (s) =>
                                                          e("div", {
                                                              key: s,
                                                              class: "h-12 bg-gray-200 rounded",
                                                          })
                                                      ),
                                                      64
                                                  )),
                                              ]),
                                          ]),
                                      ]))
                                    : u.value.length === 0 && !w.value
                                    ? (n(),
                                      i("div", Ye, [
                                          t[31] ||
                                              (t[31] = e(
                                                  "svg",
                                                  {
                                                      class: "mx-auto h-16 w-16 text-gray-400 mb-4",
                                                      fill: "none",
                                                      stroke: "currentColor",
                                                      viewBox: "0 0 24 24",
                                                  },
                                                  [
                                                      e("path", {
                                                          "stroke-linecap":
                                                              "round",
                                                          "stroke-linejoin":
                                                              "round",
                                                          "stroke-width": "2",
                                                          d: "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2",
                                                      }),
                                                  ],
                                                  -1
                                              )),
                                          t[32] ||
                                              (t[32] = e(
                                                  "h3",
                                                  {
                                                      class: "mt-2 text-lg font-medium text-gray-900",
                                                  },
                                                  " No tasks yet ",
                                                  -1
                                              )),
                                          t[33] ||
                                              (t[33] = e(
                                                  "p",
                                                  {
                                                      class: "mt-1 text-sm text-gray-500",
                                                  },
                                                  " Get started by creating your first task. ",
                                                  -1
                                              )),
                                          e("div", { class: "mt-6" }, [
                                              e(
                                                  "button",
                                                  {
                                                      onClick: h,
                                                      class: "inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200",
                                                      "aria-label":
                                                          "Create new task",
                                                  },
                                                  t[30] ||
                                                      (t[30] = [
                                                          e(
                                                              "svg",
                                                              {
                                                                  class: "-ml-1 mr-2 h-5 w-5",
                                                                  fill: "none",
                                                                  stroke: "currentColor",
                                                                  viewBox:
                                                                      "0 0 24 24",
                                                              },
                                                              [
                                                                  e("path", {
                                                                      "stroke-linecap":
                                                                          "round",
                                                                      "stroke-linejoin":
                                                                          "round",
                                                                      "stroke-width":
                                                                          "2",
                                                                      d: "M12 4v16m8-8H4",
                                                                  }),
                                                              ],
                                                              -1
                                                          ),
                                                          k(" New Task ", -1),
                                                      ])
                                              ),
                                          ]),
                                      ]))
                                    : u.value.length === 0 && w.value
                                    ? (n(),
                                      i("div", Ge, [
                                          t[34] ||
                                              (t[34] = e(
                                                  "svg",
                                                  {
                                                      class: "mx-auto h-16 w-16 text-gray-400 mb-4",
                                                      fill: "none",
                                                      stroke: "currentColor",
                                                      viewBox: "0 0 24 24",
                                                  },
                                                  [
                                                      e("path", {
                                                          "stroke-linecap":
                                                              "round",
                                                          "stroke-linejoin":
                                                              "round",
                                                          "stroke-width": "2",
                                                          d: "M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z",
                                                      }),
                                                  ],
                                                  -1
                                              )),
                                          t[35] ||
                                              (t[35] = e(
                                                  "h3",
                                                  {
                                                      class: "mt-2 text-lg font-medium text-gray-900",
                                                  },
                                                  " No tasks found ",
                                                  -1
                                              )),
                                          t[36] ||
                                              (t[36] = e(
                                                  "p",
                                                  {
                                                      class: "mt-1 text-sm text-gray-500",
                                                  },
                                                  " Try adjusting your search criteria or clear filters. ",
                                                  -1
                                              )),
                                          e("div", { class: "mt-6" }, [
                                              e(
                                                  "button",
                                                  {
                                                      onClick: B,
                                                      class: "inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200",
                                                      "aria-label":
                                                          "Clear filters",
                                                  },
                                                  " Clear Filters "
                                              ),
                                          ]),
                                      ]))
                                    : (n(),
                                      i("div", Je, [
                                          t[38] ||
                                              (t[38] = e(
                                                  "div",
                                                  {
                                                      class: "bg-gradient-to-r from-gray-700 to-gray-900 px-6 py-4",
                                                  },
                                                  [
                                                      e(
                                                          "h2",
                                                          {
                                                              class: "text-xl font-semibold text-white",
                                                          },
                                                          "Tasks List"
                                                      ),
                                                  ],
                                                  -1
                                              )),
                                          e("div", Ke, [
                                              d.value
                                                  ? (n(),
                                                    i(
                                                        "div",
                                                        Qe,
                                                        t[37] ||
                                                            (t[37] = [
                                                                e(
                                                                    "div",
                                                                    {
                                                                        class: "animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600",
                                                                    },
                                                                    null,
                                                                    -1
                                                                ),
                                                            ])
                                                    ))
                                                  : U("", !0),
                                              C(
                                                  ie,
                                                  {
                                                      data: u.value,
                                                      columns: q,
                                                      filters: O,
                                                      onCreate: h,
                                                      onView: I,
                                                      onEdit: H,
                                                      onDelete: $,
                                                      "aria-label":
                                                          "Tasks list",
                                                  },
                                                  {
                                                      title: g(
                                                          ({ item: s }) => [
                                                              e(
                                                                  "div",
                                                                  Re,
                                                                  l(s.title),
                                                                  1
                                                              ),
                                                          ]
                                                      ),
                                                      priority: g(
                                                          ({ item: s }) => [
                                                              e(
                                                                  "span",
                                                                  {
                                                                      class: S([
                                                                          te(
                                                                              s.priority
                                                                          ),
                                                                          "px-2 py-1 text-xs font-medium rounded-full",
                                                                      ]),
                                                                  },
                                                                  l(
                                                                      Z(
                                                                          s.priority
                                                                      )
                                                                  ),
                                                                  3
                                                              ),
                                                          ]
                                                      ),
                                                      status: g(
                                                          ({ item: s }) => [
                                                              e(
                                                                  "span",
                                                                  {
                                                                      class: S([
                                                                          ee(
                                                                              s.status
                                                                          ),
                                                                          "px-2 py-1 text-xs font-medium rounded-full",
                                                                      ]),
                                                                  },
                                                                  l(
                                                                      X(
                                                                          s.status
                                                                      )
                                                                  ),
                                                                  3
                                                              ),
                                                          ]
                                                      ),
                                                      due_date: g(
                                                          ({ item: s }) => [
                                                              e(
                                                                  "span",
                                                                  {
                                                                      class: S([
                                                                          se(
                                                                              s.due_date,
                                                                              s.status
                                                                          ),
                                                                          "text-sm font-medium",
                                                                      ]),
                                                                  },
                                                                  l(
                                                                      W(
                                                                          s.due_date
                                                                      )
                                                                  ),
                                                                  3
                                                              ),
                                                          ]
                                                      ),
                                                      assigned_to: g(
                                                          ({ item: s }) => [
                                                              k(
                                                                  l(
                                                                      s
                                                                          .assigned_to
                                                                          ?.full_name ||
                                                                          s
                                                                              .assigned_to
                                                                              ?.name ||
                                                                          "Unassigned"
                                                                  ),
                                                                  1
                                                              ),
                                                          ]
                                                      ),
                                                      estimated_hours: g(
                                                          ({ item: s }) => [
                                                              k(
                                                                  l(
                                                                      s.estimated_hours ||
                                                                          "-"
                                                                  ) + " hrs ",
                                                                  1
                                                              ),
                                                          ]
                                                      ),
                                                      _: 1,
                                                  },
                                                  8,
                                                  ["data"]
                                              ),
                                          ]),
                                          e("div", We, [
                                              C(
                                                  de,
                                                  {
                                                      links: a.tasks.links,
                                                      from: a.tasks.from,
                                                      to: a.tasks.to,
                                                      total: a.tasks.total,
                                                      onNavigate: Y,
                                                      class: "bg-gray-50 px-6 py-3",
                                                  },
                                                  null,
                                                  8,
                                                  [
                                                      "links",
                                                      "from",
                                                      "to",
                                                      "total",
                                                  ]
                                              ),
                                          ]),
                                      ])),
                            ]),
                            C(
                                ue,
                                {
                                    show: j.value,
                                    title: E.value
                                        ? "Edit Task"
                                        : "Create New Task",
                                    fields: z,
                                    "initial-data": D.value,
                                    loading: d.value,
                                    onClose: G,
                                    onSubmit: J,
                                    ref: "formModal",
                                },
                                null,
                                8,
                                ["show", "title", "initial-data", "loading"]
                            ),
                        ]),
                        _: 1,
                    })
                )
            );
        },
    };
export { lt as default };
