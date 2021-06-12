import { User } from '../types/user';
import axios from 'axios';

export async function fetchUser(cookies: { [name: string]: string }): Promise<User | null> {
  const cookieName = process.env.NEXT_SERVER_SIDE_COOKIE_NAME;

  if (!(cookieName in cookies)) {
    return null;
  }

  try {
    const response = await axios.get(`${process.env.NEXT_SERVER_SIDE_BACKEND_URL}/user`, {
      headers: {
        Cookie: `${cookieName}=${cookies[cookieName]}`,
      },
    });

    return response.data;
  } catch (e) {
    return null;
  }
}
