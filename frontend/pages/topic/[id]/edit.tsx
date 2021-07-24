import axiosClient from '../../../src/api';
import { GetServerSidePropsContext, GetServerSidePropsResult } from 'next';
import { fetchUser } from '../../../src/server/fetchUser';
import { serverSideTranslations } from 'next-i18next/serverSideTranslations';
import { Topic } from '../../../src/types/topic';
import { User } from '../../../src/types/user';
import { topicForm } from '../../../src/components/topic-form';
import { PageComponent } from '../../../src/types/PageComponent';

type Props = {
  user: User;
};

export async function getServerSideProps(context: GetServerSidePropsContext): Promise<GetServerSidePropsResult<Props>> {
  const user = await fetchUser(context.req.cookies);

  if (user === null) {
    return { redirect: { destination: '/login', permanent: false } };
  }

  return {
    props: { user, ...(await serverSideTranslations('de', ['common', 'topic'])) },
  };
}

const EditTopic: PageComponent<void> = topicForm(
  'headlineEdit',
  'buttonEdit',
  async (router, formik) => {
    const id = router.query.id;
    const response = await axiosClient.get<Topic>(`/topic/${id}`);
    for (const [name, value] of Object.entries(response.data)) {
      formik.setFieldValue(name, value);
    }
  },
  async (router, values) => {
    const id = router.query.id;
    await axiosClient.put(`/topic/${id}`, values);
    router.push('/topic/' + id);
  },
);

EditTopic.layout = 'main';
export default EditTopic;
