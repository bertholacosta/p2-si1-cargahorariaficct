export interface Auth {
    user: User;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
};

export interface Rol {
    id: number;
    nombre: string;
    permisos?: Permiso[];
}

export interface Permiso {
    id: number;
    nombre: string;
    slug: string;
    modulo: string;
    descripcion?: string | null;
}

export interface User {
    id: number;
    username: string;
    email: string;
    id_rol?: number;
    rol?: Rol;
    email_verified_at?: string | null;
    created_at?: string;
    updated_at?: string;
}
