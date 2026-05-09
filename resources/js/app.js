import "./bootstrap";
import "bootstrap";
import $ from "jquery";
import DataTable from "datatables.net-bs5";
import "datatables.net-responsive-bs5";
import "datatables.net-buttons-bs5";
import "datatables.net-buttons/js/buttons.html5.mjs";
import "datatables.net-buttons/js/buttons.print.mjs";
import JSZip from "jszip";
import pdfMake from "pdfmake/build/pdfmake";
import pdfFonts from "pdfmake/build/vfs_fonts";
import select2Factory from "select2";

window.$ = window.jQuery = $;
window.JSZip = JSZip;
pdfMake.vfs = pdfFonts;

if (DataTable.Buttons) {
    DataTable.Buttons.jszip(JSZip);
    DataTable.Buttons.pdfMake(pdfMake);
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
        const canExport = String($table.data("can-export") || "0") === "1";
        const syncTicketScopeFilters = () => {
            const scope = $filters.find(".js-ticket-scope").val();

            $filters.find("[data-visible-scopes]").each(function () {
                const $field = $(this);
                const isVisible = String($field.data("visible-scopes") || "")
                    .split(/\s+/)
                    .includes(scope);

                $field.toggleClass("d-none", !isVisible);

                if (!isVisible) {
                    $field.find(":input[name]").val("").trigger("change.select2");
                }
            });
        };
        const syncTicketTabCounts = (counts = {}) => {
            $panel.find(".js-ticket-tab-count").each(function () {
                const key = $(this).data("ticket-scope-count");
                const value = Number(counts[key] || 0);

                $(this).text(value.toLocaleString());
            });
        };
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
            buttons: canExport ? [
                { extend: "excelHtml5", text: '<i class="bi bi-file-earmark-excel me-1"></i>Excel', className: "btn btn-sm btn-primary text-white" },
                { extend: "pdfHtml5", text: '<i class="bi bi-file-earmark-pdf me-1"></i>PDF', className: "btn btn-sm btn-primary text-white", orientation: "landscape", pageSize: "A4" },
                { extend: "csvHtml5", text: '<i class="bi bi-filetype-csv me-1"></i>CSV', className: "btn btn-sm btn-primary text-white" },
                { extend: "print", text: '<i class="bi bi-printer me-1"></i>Print', className: "btn btn-sm btn-primary text-white" },
                { extend: "copyHtml5", text: '<i class="bi bi-copy me-1"></i>Copy', className: "btn btn-sm btn-primary text-white" },
            ] : [],
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

        $table.on("xhr.dt", function (_event, _settings, json) {
            if (json?.tabCounts) {
                syncTicketTabCounts(json.tabCounts);
            }
        });

        syncTicketScopeFilters();

        $panel.find(".js-ticket-scope-tab").on("click", function () {
            const scope = $(this).data("ticket-scope");

            $panel.find(".js-ticket-scope-tab")
                .removeClass("active")
                .attr("aria-selected", "false");
            $(this)
                .addClass("active")
                .attr("aria-selected", "true");

            $filters.find(".js-ticket-scope").val(scope);
            syncTicketScopeFilters();
            dataTable.draw();
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
            $filters.find(":input[name]").not(".js-ticket-scope").val("").trigger("change.select2");
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

    $(".erp-help-editor").each(function () {
        const $wrap = $(this);
        const $editor = $wrap.find(".js-help-editor");
        const $input = $wrap.find(".js-help-editor-input");
        const sync = () => $input.val($editor.html());

        $wrap.find("[data-help-command]").on("click", function () {
            const command = $(this).data("help-command");
            let value = $(this).data("help-value") || null;

            $editor.trigger("focus");

            if (command === "createLink") {
                value = window.prompt("URL");
                if (!value) return;
            }

            if (command === "insertImage") {
                value = window.prompt("Image URL");
                if (!value) return;
            }

            if (command === "insertTable") {
                document.execCommand("insertHTML", false, "<table><thead><tr><th>Column</th><th>Column</th></tr></thead><tbody><tr><td>Value</td><td>Value</td></tr></tbody></table>");
                sync();
                return;
            }

            if (command === "insertCode") {
                document.execCommand("insertHTML", false, "<pre><code>// code</code></pre><p><br></p>");
                sync();
                return;
            }

            document.execCommand(command, false, value);
            sync();
        });

        $editor.on("input blur paste keyup", sync);
        $wrap.closest("form").on("submit", sync);
    });

    $(".js-sidebar-toggle").on("click", function () {
        setSidebarState(!document.body.classList.contains("erp-sidebar-open"));
    });

    $(".js-password-toggle").on("click", function () {
        const $button = $(this);
        const $input = $button.closest(".input-group").find(".js-password-input");
        const showing = $input.attr("type") === "text";

        $input.attr("type", showing ? "password" : "text");
        $button.attr("title", showing ? "Show password" : "Hide password");
        $button.find("i").toggleClass("bi-eye", showing).toggleClass("bi-eye-slash", !showing);
    });

    $(".js-loading-form").on("submit", function () {
        const $form = $(this);
        const $button = $form.find(".erp-auth-submit");

        $button.prop("disabled", true);
        $button.find(".js-submit-label").addClass("d-none");
        $button.find(".js-submit-loading").removeClass("d-none");
    });

    $(".js-profile-avatar-input").on("change", function () {
        const file = this.files?.[0];

        if (!file) {
            return;
        }

        const previewUrl = window.URL.createObjectURL(file);

        $(".js-profile-avatar-preview")
            .attr("src", previewUrl)
            .removeClass("d-none");
        $(".js-profile-avatar-fallback").addClass("d-none");
    });

    $(".js-role-permission-form").on("click", ".js-permission-select-all", function () {
        const $form = $(this).closest(".js-role-permission-form");
        const target = $(this).data("target");
        const $checks = $form.find(target).filter(":enabled");
        const shouldCheck = $checks.filter(":checked").length !== $checks.length;

        $checks.prop("checked", shouldCheck).trigger("change");
    });

    $(".js-role-permission-form").on("change", ".js-permission-check", function () {
        const $form = $(this).closest(".js-role-permission-form");
        const target = $(this).data("module-target");

        if (!target) {
            return;
        }

        const $moduleChecks = $form.find(target);
        const checked = $moduleChecks.filter(":checked").length;
        const total = $moduleChecks.length;

        $form.find(`.erp-module-permission-count[data-module-target="${target}"]`).text(`${checked}/${total}`);
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
