import type { Lang } from "@frontend/lib/i18n/config";
import type { Profile } from "@frontend/lib/profile";
import type { Site } from "@frontend/lib/site";
import type { Cv, Experience, Post, Project } from "@frontend/lib/types";

// Props the Laravel controllers send to every public page.
export type SitePageProps = {
  lang: Lang;
  siteUrl: string;
  site: Site;
  profile: Profile;
  posts: Post[];
  projects: Project[];
  experiences: Experience[];
  cv: Cv | null;
  photoUrl: string | null;
};
