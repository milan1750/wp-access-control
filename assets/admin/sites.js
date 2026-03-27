window.WPAC = window.WPAC || {};

(function ($) {
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

  function slugify(text) {
    return (text || "")
      .toLowerCase()
      .trim()
      .replace(/&/g, " and ")
      .replace(/[^a-z0-9]+/g, "_")
      .replace(/^_+|_+$/g, "");
  }

  function bindSiteSlug() {
    $(document).on("input", "#wpac-site-name", function () {
      let name = $(this).val();
      $("#wpac-site-slug").val(name ? slugify(name) : "");
    });
  }

  function bindSiteSave() {
    $(document).on("click", "#wpac-save-site", function () {
      let btn = $(this);
      let id = valSafe("#wpac-site-id-hidden"),
        entity_id = valSafe("#wpac-site-entity"),
        site_id = valSafe("#wpac-site-id"),
        name = valSafe("#wpac-site-name"),
        slug = valSafe("#wpac-site-slug"),
        location = valSafe("#wpac-site-location");

      if (!entity_id || !site_id || !name) {
        Swal.fire("Warning", "Entity, Site ID, and Name required", "warning");
        return;
      }

      setLoading(btn, true);

      $.post(WPAC.ajax, {
        action: "wpac_save_site",
        nonce: WPAC.nonce,
        id: id,
        entity_id: entity_id,
        site_id: site_id,
        name: name,
        slug: slug,
        location: location,
      })
        .done(function (res) {
          if (!res || !res.success) {
            Swal.fire("Error", res?.data || "Failed", "error");
            return;
          }

          const siteData = res.data;
          const newItemHtml = `
            <li class="wpac-item"
                data-id="${siteData.id}"
                data-entity="${entity_id}"
                data-siteid="${site_id}"
                data-name="${name}"
                data-slug="${slug}"
                data-location="${location}">
                <div class="wpac-item-info">
                  <strong>${name}</strong><br>
                  <small>${location}</small><br>
                  <small><b>Site ID:</b> ${site_id} • <b>Slug:</b> ${slug}</small>
                </div>
                <div class="wpac-item-actions">
                  <button class="wpac-edit-site button">Edit</button>
                  <button class="wpac-delete button" data-id="${siteData.id}">Delete</button>
                </div>
            </li>`;

          if (id) {
            // Update existing
            $(`#wpac-site-list li[data-id="${id}"]`).replaceWith(newItemHtml);
            Swal.fire("Success", "Site updated", "success");
          } else {
            // Add new
            $("#wpac-site-list").prepend(newItemHtml);
            Swal.fire("Success", "Site added", "success");
          }

          // Reset form
          $("#wpac-site-id-hidden,#wpac-site-id,#wpac-site-name,#wpac-site-slug,#wpac-site-location,#wpac-site-entity").val("");
          btn.text("Add Site");
          $("#wpac-cancel-site").hide();
        })
        .fail(function () {
          Swal.fire("Error", "Server error", "error");
        })
        .always(function () {
          setLoading(btn, false);
        });
    });
  }

  function bindSiteEdit() {
    $(document).on("click", ".wpac-edit-site", function () {
      let item = $(this).closest(".wpac-item");
      $("#wpac-site-id-hidden").val(item.data("id"));
      $("#wpac-site-entity").val(item.data("entity"));
      $("#wpac-site-id").val(item.data("siteid"));
      $("#wpac-site-name").val(item.data("name"));
      $("#wpac-site-slug").val(item.data("slug"));
      $("#wpac-site-location").val(item.data("location"));

      $("#wpac-save-site").text("Update Site");
      $("#wpac-cancel-site").show();
    });
  }

  function bindSiteCancel() {
    $(document).on("click", "#wpac-cancel-site", function () {
      $("#wpac-site-id-hidden,#wpac-site-id,#wpac-site-name,#wpac-site-slug,#wpac-site-location,#wpac-site-entity").val("");
      $("#wpac-save-site").text("Add Site");
      $(this).hide();
    });
  }

  function bindSiteDelete() {
    $(document).on("click", ".wpac-delete", function () {
      let btn = $(this),
        item = btn.closest(".wpac-item"),
        id = item.data("id");

      Swal.fire({
        title: "Are you sure?",
        text: "This cannot be undone",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete",
      }).then((res) => {
        if (!res.isConfirmed) return;
        $.post(WPAC.ajax, {
          action: "wpac_delete_site",
          nonce: WPAC.nonce,
          id: id,
        })
          .done((res) => {
            if (!res || !res.success) {
              Swal.fire("Error", res?.data || "Delete failed", "error");
              return;
            }
            Swal.fire("Deleted", "", "success").then(() => item.remove());
          })
          .fail(() => Swal.fire("Error", "Server error", "error"));
      });
    });
  }

  function init() {
    bindSiteSlug();
    bindSiteSave();
    bindSiteEdit();
    bindSiteCancel();
    bindSiteDelete();
  }

  $(function () {
    init();
  });
})(jQuery);
