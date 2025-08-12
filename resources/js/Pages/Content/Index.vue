<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Content Management
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Search and Filter Bar -->
                <SearchFilter
                    v-model="filters"
                    :filters="['search', 'status', 'type', 'date']"
                    :status-options="statusOptions"
                    :type-options="typeOptions"
                    @apply="applyFilters"
                    @clear="clearFilters"
                />

                <!-- Data Table -->
                <DataTable
                    :data="posts"
                    :columns="columns"
                    :filters="tableFilters"
                    @create="createPost"
                    @view="viewPost"
                    @edit="editPost"
                    @delete="deletePost"
                >
                    <template #title="{ item }">
                        <div class="font-medium text-gray-900">
                            {{ item.title }}
                        </div>
                    </template>

                    <template #type="{ item }">
                        <span
                            :class="getTypeClass(item.type)"
                            class="px-2 py-1 text-xs font-medium rounded-full"
                        >
                            {{ formatType(item.type) }}
                        </span>
                    </template>

                    <template #status="{ item }">
                        <span
                            :class="getStatusClass(item.status)"
                            class="px-2 py-1 text-xs font-medium rounded-full"
                        >
                            {{ formatStatus(item.status) }}
                        </span>
                    </template>

                    <template #scheduled_at="{ item }">
                        {{
                            item.scheduled_at
                                ? formatDate(item.scheduled_at)
                                : "N/A"
                        }}
                    </template>

                    <template #user="{ item }">
                        {{ item.user?.name || "System" }}
                    </template>
                </DataTable>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <FormModal
            :show="showModal"
            :title="isEdit ? 'Edit Content' : 'Create New Content'"
            :fields="formFields"
            :initial-data="form"
            :loading="loading"
            @close="closeModal"
            @submit="handleSubmit"
            ref="formModal"
        />
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import DataTable from "@/Components/DataTable.vue";
import FormModal from "@/Components/FormModal.vue";
import SearchFilter from "@/Components/SearchFilter.vue";
import axios from "axios";

// Reactive data
const posts = ref([]);
const categories = ref([]);
const loading = ref(false);
const showModal = ref(false);
const isEdit = ref(false);
const form = ref({});
const filters = ref({
    search: "",
    status: "",
    type: "",
    date_from: "",
    date_to: "",
});

// Table columns
const columns = [
    { key: "id", label: "ID", sortable: true },
    { key: "title", label: "Title", sortable: true },
    { key: "type", label: "Type", sortable: true },
    { key: "status", label: "Status", sortable: true },
    { key: "scheduled_at", label: "Scheduled", type: "date", sortable: true },
    { key: "user", label: "Author", sortable: true },
];

// Status options
const statusOptions = [
    { value: "draft", label: "Draft" },
    { value: "published", label: "Published" },
    { value: "scheduled", label: "Scheduled" },
];

// Type options
const typeOptions = [
    { value: "blog", label: "Blog" },
    { value: "social_media", label: "Social Media" },
    { value: "email", label: "Email" },
    { value: "newsletter", label: "Newsletter" },
];

// Form fields
const formFields = [
    { name: "title", label: "Title", type: "text", required: true },
    {
        name: "content",
        label: "Content",
        type: "textarea",
        required: true,
        rows: 5,
    },
    {
        name: "type",
        label: "Type",
        type: "select",
        required: true,
        options: typeOptions,
    },
    {
        name: "status",
        label: "Status",
        type: "select",
        required: true,
        options: statusOptions,
    },
    {
        name: "category_id",
        label: "Category",
        type: "select",
        required: false,
        options: [],
    },
    {
        name: "scheduled_at",
        label: "Scheduled Date",
        type: "datetime-local",
        required: false,
    },
    {
        name: "tags",
        label: "Tags",
        type: "text",
        required: false,
        placeholder: "Enter tags separated by commas",
    },
    {
        name: "meta_description",
        label: "Meta Description",
        type: "textarea",
        required: false,
        rows: 2,
    },
    {
        name: "seo_keywords",
        label: "SEO Keywords",
        type: "text",
        required: false,
        placeholder: "Enter keywords separated by commas",
    },
];

// Table filters
const tableFilters = [
    { value: "draft", label: "Draft" },
    { value: "published", label: "Published" },
    { value: "scheduled", label: "Scheduled" },
];

// Methods
const fetchPosts = async () => {
    loading.value = true;
    try {
        const params = new URLSearchParams();
        Object.keys(filters.value).forEach((key) => {
            if (filters.value[key]) params.append(key, filters.value[key]);
        });

        const response = await axios.get(`/api/content-posts?${params}`);
        posts.value = response.data.data;
    } catch (error) {
        console.error("Error fetching posts:", error);
    } finally {
        loading.value = false;
    }
};

const fetchCategories = async () => {
    try {
        const response = await axios.get("/api/categories");
        categories.value = response.data.data;
        // Update form fields with categories
        const categoryField = formFields.find((f) => f.name === "category_id");
        if (categoryField) {
            categoryField.options = categories.value.map((category) => ({
                value: category.id,
                label: category.name,
            }));
        }
    } catch (error) {
        console.error("Error fetching categories:", error);
    }
};

const createPost = () => {
    isEdit.value = false;
    form.value = {
        title: "",
        content: "",
        type: "blog",
        status: "draft",
        category_id: "",
        scheduled_at: "",
        tags: "",
        meta_description: "",
        seo_keywords: "",
    };
    showModal.value = true;
};

const editPost = (post) => {
    isEdit.value = true;
    form.value = {
        ...post,
        tags: Array.isArray(post.tags) ? post.tags.join(", ") : post.tags || "",
        scheduled_at: post.scheduled_at
            ? post.scheduled_at.replace(" ", "T")
            : "",
    };
    showModal.value = true;
};

const viewPost = (post) => {
    router.visit(`/content/${post.id}`);
};

const deletePost = async (post) => {
    if (confirm("Are you sure you want to delete this content?")) {
        try {
            await axios.delete(`/api/content-posts/${post.id}`);
            await fetchPosts();
        } catch (error) {
            console.error("Error deleting post:", error);
        }
    }
};

const handleSubmit = async (formData) => {
    loading.value = true;
    try {
        // Handle tags conversion
        if (formData.tags) {
            formData.tags = formData.tags
                .split(",")
                .map((tag) => tag.trim())
                .filter((tag) => tag);
        }

        if (isEdit.value) {
            await axios.put(`/api/content-posts/${form.value.id}`, formData);
        } else {
            await axios.post("/api/content-posts", formData);
        }
        await fetchPosts();
        closeModal();
    } catch (error) {
        if (error.response?.status === 422) {
            const validationErrors = error.response.data.errors;
            if (formModal.value) {
                formModal.value.setErrors(validationErrors);
            }
        } else {
            console.error("Error saving post:", error);
        }
    } finally {
        loading.value = false;
    }
};

const closeModal = () => {
    showModal.value = false;
    form.value = {};
    if (formModal.value) {
        formModal.value.resetForm();
    }
};

const applyFilters = () => {
    fetchPosts();
};

const clearFilters = () => {
    filters.value = {
        search: "",
        status: "",
        type: "",
        date_from: "",
        date_to: "",
    };
    fetchPosts();
};

// Utility functions
const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};

const formatStatus = (status) => {
    return status.charAt(0).toUpperCase() + status.slice(1).replace("_", " ");
};

const formatType = (type) => {
    return type.charAt(0).toUpperCase() + type.slice(1).replace("_", " ");
};

const getStatusClass = (status) => {
    const classes = {
        draft: "bg-gray-100 text-gray-800",
        published: "bg-green-100 text-green-800",
        scheduled: "bg-blue-100 text-blue-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
};

const getTypeClass = (type) => {
    const classes = {
        blog: "bg-purple-100 text-purple-800",
        social_media: "bg-blue-100 text-blue-800",
        email: "bg-green-100 text-green-800",
        newsletter: "bg-orange-100 text-orange-800",
    };
    return classes[type] || "bg-gray-100 text-gray-800";
};

// Lifecycle
onMounted(() => {
    fetchPosts();
    fetchCategories();
});
</script>
