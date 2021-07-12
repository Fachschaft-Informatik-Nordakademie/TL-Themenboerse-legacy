import { UserProfile } from './userProfile';

export type UserType = 'LDAP' | 'EXTERNAL';

export type User = {
  id: number;
  type: UserType;
  email: string;
  ldapUsername?: string;
  ldapDn?: string;
  roles: string[];
  admin: boolean;
  profile: UserProfile;
};
