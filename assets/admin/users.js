window.WPAC = window.WPAC || {};
console.log(window.WPAC);
(function ($) {

    /* =========================
       HELPERS
    ========================== */
    function setLoading(btn, state, text = "Saving...") {
        if (!btn || !btn.length) return;
        if (state) {
            btn.data("old", btn.text());
            btn.prop("disabled", true).text(text);
        } else {
            btn.prop("disabled", false).text(btn.data("old"));
        }
    }

    function valSafe(selector) {
        return ($(selector).val() || "").toString().trim();
    }

    /* =========================
       APPLY CAPABILITIES TO CHECKBOXES
    ========================== */
    function applyCapabilities(role, caps) {
		console.log(role, caps);

        let effectiveCaps = Array.isArray(caps) ? caps.map(c => c.toString()) : [];

        // Uncheck all first
        $(".wpac-capability-checkbox, .wpac-module-checkbox").prop('checked', false);

        // Check only the capabilities in effectiveCaps
        $(".wpac-capability-checkbox").each(function () {
            let val = $(this).val().toString();
            if (effectiveCaps.includes(val)) {
                $(this).prop('checked', true);
            }
        });

        // Update module "Select All" checkboxes
        $(".wpac-module-checkbox").each(function () {
            let module = $(this).data('module');
            let moduleCaps = $(".wpac-capability-checkbox[data-module='" + module + "']");
            let checkedCaps = moduleCaps.filter(':checked');
            $(this).prop('checked', moduleCaps.length && moduleCaps.length === checkedCaps.length);
        });
    }

    /* =========================
       LOAD USER INTO EDITOR
    ========================== */
    function loadUser(userId) {
        let user = WPAC.userOverrides[userId] || { role: '', scope: 'global', capabilities: [] };
        let caps = user.capabilities;

        // Fallback to role default capabilities if user override is empty
        if ((!caps || !caps.length) && user.role) {
            caps = WPAC.rolesCaps[user.role] || [];
        }

        // Flatten in case of nested arrays
        let flatCaps = [];
        if (Array.isArray(caps)) {
            flatCaps = caps;
        } else if (typeof caps === 'object') {
            Object.values(caps).forEach(arr => {
                if (Array.isArray(arr)) flatCaps = flatCaps.concat(arr);
            });
        }

        $("#wpac-user-id").val(userId);
        $("#wpac-role").val(user.role);
        $("#wpac-scope").val(user.scope || 'global');

        applyCapabilities(user.role, flatCaps);

        $("#wpac-empty").hide();
        $("#wpac-editor").show();

        // Highlight active user card
        $(".wpac-user-card").removeClass("active");
        $('.wpac-user-card[data-user="' + userId + '"]').addClass("active");
    }

    /* =========================
       USER CARD CLICK
    ========================== */
    function bindUserCardClick() {
        $(document).on("click", ".wpac-user-card", function (e) {
            e.preventDefault();
            let userId = $(this).data("user");
            loadUser(userId);
        });
    }

    /* =========================
       MODULE - CAPABILITY CHECKBOX LOGIC
    ========================== */
    function bindCheckboxLogic() {
        // Module checkbox toggle
        $(document).on("change", ".wpac-module-checkbox", function () {
            let module = $(this).data("module");
            let checked = $(this).is(":checked");
            $(".wpac-capability-checkbox[data-module='" + module + "']").prop("checked", checked);
        });

        // Individual capability checkbox toggle -> update module state
        $(document).on("change", ".wpac-capability-checkbox", function () {
            let module = $(this).data("module");
            let allCaps = $(".wpac-capability-checkbox[data-module='" + module + "']");
            let checkedCaps = allCaps.filter(":checked");
            $(".wpac-module-checkbox[data-module='" + module + "']").prop("checked", allCaps.length === checkedCaps.length);
        });
    }

    /* =========================
       ROLE CHANGE
    ========================== */
    function bindRoleChange() {
        $(document).on("change", "#wpac-role", function () {
            let userId = valSafe("#wpac-user-id");
            if (!userId) return;

            let role = $(this).val();
            let userData = WPAC.userOverrides[userId] || { capabilities: [] };
            let caps = (userData.role === role && userData.capabilities.length) ? userData.capabilities : WPAC.rolesCaps[role] || [];

            // Flatten caps
            let flatCaps = [];
            if (Array.isArray(caps)) {
                flatCaps = caps;
            } else if (typeof caps === 'object') {
                Object.values(caps).forEach(arr => {
                    if (Array.isArray(arr)) flatCaps = flatCaps.concat(arr);
                });
            }

            applyCapabilities(role, flatCaps);
        });
    }

    /* =========================
       SAVE USER CAPABILITIES
    ========================== */
    function bindUserSave() {
        $(document).on("click", "#wpac-save", function () {
            let btn = $(this);
            let userId = valSafe("#wpac-user-id");
            let role = valSafe("#wpac-role");
            let scope = valSafe("#wpac-scope");

            if (!userId || !role) {
                Swal.fire("Warning", "User and Role required", "warning");
                return;
            }

            // Gather selected capabilities
            let capabilities = [];
            $(".wpac-capability-checkbox:checked").each(function () {
                capabilities.push($(this).val());
            });

            setLoading(btn, true);

            $.post(WPAC.ajax, {
                action: "wpac_save_user_caps",
                nonce: WPAC.nonce,
                user_id: userId,
                role: role,
                scope: scope,
                capabilities: JSON.stringify(capabilities)
            })
                .done(function (res) {
                    if (!res || !res.success) {
                        Swal.fire("Error", res?.data || "Failed", "error");
                        return;
                    }
                    WPAC.userOverrides[userId] = { role, scope, capabilities };
                    Swal.fire("Success", "User capabilities updated", "success");
                })
                .fail(function () {
                    Swal.fire("Error", "Server error", "error");
                })
                .always(function () {
                    setLoading(btn, false);
                });
        });
    }

    /* =========================
       REVOKE USER
    ========================== */
    function bindUserRevoke() {
        $(document).on("click", "#wpac-revoke", function () {
            let userId = valSafe("#wpac-user-id");
            if (!userId) return;

            Swal.fire({
                title: "Are you sure?",
                text: "This will remove all role assignments and capabilities",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, revoke"
            }).then(function (result) {
                if (!result.isConfirmed) return;

                $.post(WPAC.ajax, {
                    action: "wpac_revoke_user_caps",
                    nonce: WPAC.nonce,
                    user_id: userId
                })
                    .done(function (res) {
                        if (!res || !res.success) {
                            Swal.fire("Error", res?.data || "Failed", "error");
                            return;
                        }
                        Swal.fire("Revoked", "", "success").then(() => location.reload());
                    })
                    .fail(function () {
                        Swal.fire("Error", "Server error", "error");
                    });
            });
        });
    }

    /* =========================
       INIT
    ========================== */
    function init() {
        bindUserCardClick();
        bindCheckboxLogic();
        bindRoleChange();
        bindUserSave();
        bindUserRevoke();

        // Auto-load first user if exists
        let firstUser = Object.keys(WPAC.userOverrides)[0];
        if (firstUser) loadUser(firstUser);
    }

    $(function () { init(); });

})(jQuery);
