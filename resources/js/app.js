import "./bootstrap";
import "bootstrap";
import $ from "jquery";
import DataTable from "datatables.net-bs5";
import "datatables.net-responsive-bs5";
import "datatables.net-buttons-bs5";
import "datatables.net-buttons/js/buttons.html5.mjs";
import "datatables.net-buttons/js/buttons.print.mjs";
import JSZip from "jszip";
import select2Factory from "select2";

window.$ = window.jQuery = $;
window.JSZip = JSZip;

if (DataTable.Buttons) {
    DataTable.Buttons.jszip(JSZip);
}

if (!$.fn.select2 && typeof select2Factory === "function") {
    select2Factory(window, $);
}

const parseJson = (value, fallback = []) => {
    if (!value) {
        return fallback;
    }

    try {
        return JSON.parse(value);
    } catch (error) {
        return fallback;
    }
};

const SIDEBAR_STORAGE_KEY = "erp.sidebar.open";

const setSidebarState = (isOpen) => {
    document.body.classList.toggle("erp-sidebar-open", isOpen);

    $(".js-sidebar-toggle").attr("aria-expanded", String(isOpen));
    $(".erp-sidebar-toggle-icon")
        .toggleClass("bi-list", !isOpen)
        .toggleClass("bi-chevron-left", isOpen);

    try {
        window.localStorage.setItem(SIDEBAR_STORAGE_KEY, isOpen ? "1" : "0");
    } catch (error) {
        // Sidebar state is a convenience only; ignore storage restrictions.
    }

    window.dispatchEvent(new Event("resize"));

    window.setTimeout(() => {
        if ($.fn.dataTable) {
            $.fn.dataTable
                .tables({ visible: true, api: true })
                .columns.adjust()
                .responsive.recalc();
        }
    }, 220);
};

const getStoredSidebarState = () => {
    try {
        return window.localStorage.getItem(SIDEBAR_STORAGE_KEY) === "1";
    } catch (error) {
        return false;
    }
};

window.ErpDataTable = {
    init(table) {
        const $table = $(table);
        const ajaxUrl = $table.data("ajax-url");

        if (!ajaxUrl || $.fn.DataTable.isDataTable(table)) {
            return null;
        }

        const $panel = $table.closest(".erp-panel");
        const $filters = $panel.find(".js-erp-datatable-filters");
        const configuredColumns = parseJson($table.attr("data-erp-columns"));
        const columns = [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
                className: "text-muted",
            },
            ...configuredColumns,
            {
                data: "actions",
                name: "actions",
                orderable: false,
                searchable: false,
                className: "text-nowrap",
            },
        ];

        const dataTable = $table.DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            scrollX: true,
            autoWidth: false,
            paging: true,
            pageLength: 10,
            lengthMenu: [
                [10, 25, 50, 100],
                [10, 25, 50, 100],
            ],
            dom: "<'erp-datatable-actions'B>rt<'erp-datatable-footer'<'erp-datatable-summary'li><'erp-datatable-pagination'p>>",
            buttons: [
                { extend: "excelHtml5", text: '<i class="bi bi-file-earmark-excel me-1"></i>Excel', className: "btn btn-sm btn-primary text-white" },
                { extend: "csvHtml5", text: '<i class="bi bi-filetype-csv me-1"></i>CSV', className: "btn btn-sm btn-primary text-white" },
                { extend: "print", text: '<i class="bi bi-printer me-1"></i>Print', className: "btn btn-sm btn-primary text-white" },
                { extend: "copyHtml5", text: '<i class="bi bi-copy me-1"></i>Copy', className: "btn btn-sm btn-primary text-white" },
            ],
            ajax: {
                url: ajaxUrl,
                data(data) {
                    data.keyword = $filters.find(".js-datatable-keyword").val() || "";

                    $filters.find(":input[name]").each(function () {
                        data[this.name] = $(this).val();
                    });
                },
                error(xhr) {
                    const message = xhr.responseJSON?.message || xhr.statusText || "Data gagal dimuat";
                    const colspan = columns.length;

                    $table.find("tbody").html(
                        `<tr><td colspan="${colspan}" class="text-danger py-3">DataTable error: ${message}</td></tr>`,
                    );
                },
            },
            columns,
            order: [[1, "desc"]],
            searchDelay: 250,
            language: {
                processing: "Loading...",
                lengthMenu: "_MENU_ rows",
                info: "_START_-_END_ of _TOTAL_",
                infoEmpty: "0 records",
                infoFiltered: "(filtered from _MAX_ total)",
                zeroRecords: "No matching records",
                paginate: {
                    previous: "Prev",
                    next: "Next",
                },
            },
        });

        $filters.find(".js-datatable-keyword").on("input", function () {
            dataTable.search(this.value).draw();
        });

        $filters.find(".js-datatable-filter").on("change", function () {
            dataTable.draw();
        });

        $filters.find(".js-datatable-search").on("click", function () {
            dataTable.search($filters.find(".js-datatable-keyword").val() || "");
            dataTable.draw();
        });

        $filters.find(".js-datatable-reset").on("click", function () {
            $filters.find(":input[name]").val("").trigger("change.select2");
            dataTable.search("");
            dataTable.draw();
        });

        return dataTable;
    },

    initAll() {
        $(".js-erp-datatable").each((_, table) => this.init(table));
    },
};

$(function () {
    setSidebarState(getStoredSidebarState());

    window.ErpDataTable.initAll();

    $(".js-select2").select2({
        width: "100%",
    });

    $(".js-sidebar-toggle").on("click", function () {
        setSidebarState(!document.body.classList.contains("erp-sidebar-open"));
    });

    $(".erp-nav .collapse")
        .on("show.bs.collapse", function () {
            $(`[aria-controls="${this.id}"]`)
                .find(".erp-nav-chevron")
                .removeClass("bi-chevron-right")
                .addClass("bi-chevron-down");
        })
        .on("hide.bs.collapse", function () {
            $(`[aria-controls="${this.id}"]`)
                .find(".erp-nav-chevron")
                .removeClass("bi-chevron-down")
                .addClass("bi-chevron-right");
        });

    $(document).on("keydown", function (event) {
        if (event.key === "Escape" && document.body.classList.contains("erp-sidebar-open")) {
            setSidebarState(false);
        }
    });
});
