export type UserProfile = {
  firstName: string;
  lastName: string;
  image?: string;
  biography?: string;
  company?: string;
  job?: string;
  courseOfStudy?: string;
  skills: string[];
  references: string[];
};
