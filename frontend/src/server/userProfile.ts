import axios from 'axios';
import { UserProfile } from '../types/userProfile';

export async function fetchUserProfile(
  id: number | string,
  cookies: { [name: string]: string },
): Promise<UserProfile | null> {
  const cookieName = process.env.NEXT_SERVER_SIDE_COOKIE_NAME;

  if (!(cookieName in cookies)) {
    return null;
  }

  try {
    const response = await axios.get(`${process.env.NEXT_SERVER_SIDE_BACKEND_URL}/user_profile/${id}`, {
      headers: {
        Cookie: `${cookieName}=${cookies[cookieName]}`,
      },
    });
    return response.data;
  } catch (e) {
    return null;
  }
}
